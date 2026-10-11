<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Stringable;
use TestApp\Model\Behavior\TypedExposeBehavior;
use TestApp\Model\Behavior\TypedSuperimposeBehavior;

class TypedFinderContractTest extends TestCase {

	/**
	 * @var list<string>
	 */
	protected array $fixtures = ['plugin.Expose.Users'];

	/**
	 * @return void
	 */
	public function testDirectTypedListFinder(): void {
		$users = $this->fetchTable('Users');
		$query = $users->getBehavior('Expose')->findExposedList($users->find(), keyField: 'id', valueField: 'name');
		$this->assertSame([1 => 'Foo Bar'], $query->toArray());
	}

	/**
	 * @return void
	 */
	public function testNullListKeyUsesExposedField(): void {
		$users = $this->fetchTable('Users');
		$this->assertSame(['eb25610d-7bfa-4e34-812c-ad72b100fb26' => 'Foo Bar'], $users->find('exposedList', keyField: null, valueField: 'name')->toArray());
	}

	/**
	 * @return void
	 */
	public function testTypedOverrideReceivesNamedFinderOptions(): void {
		$users = $this->fetchTable('Users');
		$users->removeBehavior('Expose');
		$users->addBehavior('Expose', ['className' => TypedExposeBehavior::class]);
		$this->assertSame([1 => 'Foo Bar'], $users->find('exposedList', keyField: 'id', valueField: 'name')->toArray());
		$this->assertSame(1, $users->getBehavior('Expose')->calls);
	}

	/**
	 * @return void
	 */
	public function testTypedSuperimposeOverrideAndStringResolver(): void {
		$users = $this->fetchTable('Users');
		$users->addBehavior('Superimpose', ['className' => TypedSuperimposeBehavior::class]);
		$uuid = 'eb25610d-7bfa-4e34-812c-ad72b100fb26';
		$behavior = $users->getBehavior('Superimpose');
		$result = $users->find()->where(['Users.id' => $uuid])->firstOrFail();
		$this->assertSame($uuid, $result->id);
		$this->assertSame([$uuid], $behavior->resolved);
		$this->assertGreaterThan(0, $behavior->calls);
		$calls = $behavior->calls;
		$this->assertSame($uuid, $behavior->findSuperimpose($users->find(superimpose: false))->firstOrFail()->id);
		$this->assertGreaterThan($calls, $behavior->calls);
		$result = $behavior->findSuperimpose($users->find(superimpose: false))->firstOrFail();
		$this->assertSame(1, $result->_id);
	}

	/**
	 * @return void
	 */
	public function testStringableUuidUsesReleasedResolver(): void {
		$users = $this->fetchTable('Users');
		$users->addBehavior('Expose.Superimpose');
		$uuid = new class implements Stringable {
			public function __toString(): string {
				return 'eb25610d-7bfa-4e34-812c-ad72b100fb26';
			}
		};
		$this->assertSame((string)$uuid, $users->find()->where(['Users.id' => $uuid])->firstOrFail()->id);
	}

}
