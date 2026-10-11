<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use TestApp\Model\Behavior\LegacyExposeBehavior;
use TestApp\Model\Behavior\LegacySuperimposeBehavior;

class ReleasedFinderContractTest extends TestCase {

	/**
	 * @var list<string>
	 */
	protected array $fixtures = ['plugin.Expose.Users'];

	/**
	 * @return void
	 */
	public function testDirectExposedListOptionsRemainSupported(): void {
		$users = $this->fetchTable('Users');
		$query = $users->getBehavior('Expose')->findExposedList($users->find(), ['keyField' => 'id', 'valueField' => 'name']);
		$this->assertSame([1 => 'Foo Bar'], $query->toArray());
	}

	/**
	 * @return void
	 */
	public function testOldListOverrideReceivesNamedFinderOptions(): void {
		$users = $this->fetchTable('Users');
		$users->removeBehavior('Expose');
		$users->addBehavior('Expose', ['className' => LegacyExposeBehavior::class]);
		$this->assertSame([1 => 'Foo Bar'], $users->find('exposedList', keyField: 'id', valueField: 'name')->toArray());
		$this->assertSame(1, $users->getBehavior('Expose')->calls);
	}

	/**
	 * @return void
	 */
	public function testOldSuperimposeOverrideAndStringResolverRemainSupported(): void {
		$users = $this->fetchTable('Users');
		$users->addBehavior('Superimpose', ['className' => LegacySuperimposeBehavior::class]);
		$uuid = 'eb25610d-7bfa-4e34-812c-ad72b100fb26';
		$behavior = $users->getBehavior('Superimpose');
		$result = $users->find()->where(['Users.id' => $uuid])->firstOrFail();
		$this->assertSame($uuid, $result->id);
		$this->assertSame([$uuid], $behavior->resolved);
		$this->assertGreaterThan(0, $behavior->calls);
		$calls = $behavior->calls;
		$this->assertSame($uuid, $behavior->findSuperimpose($users->find(), [])->firstOrFail()->id);
		$this->assertGreaterThan($calls, $behavior->calls);
	}

}
