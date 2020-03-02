<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TestApp\Model\Table\UsersTable;

class ExposeBehaviorTest extends TestCase {

	/**
	 * @var string[]
	 */
	protected $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.CustomFieldRecords',
	];

	/**
	 * @var \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior
	 */
	protected $Users;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Users = TableRegistry::getTableLocator()->get('Users', ['className' => UsersTable::class]);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$user = $this->Users->newEntity([
			'name' => 'New User',
		]);
		$this->assertNotEmpty($user->uuid);

		$this->Users->saveOrFail($user);

		$user = $this->Users->get($user->id);

		$this->assertNotEmpty($user->uuid);
	}

	/**
	 * @return void
	 */
	public function testBeforeSave() {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['on' => 'beforeSave']);

		$user = $this->Users->newEntity([
			'name' => 'New User',
		]);
		$this->assertEmpty($user->uuid);

		$this->Users->saveOrFail($user);

		$this->assertNotEmpty($user->uuid);
	}

	/**
	 * @return void
	 */
	public function testFindExposed() {
		$user = $this->Users->find()->firstOrFail();

		$uuid = $user->uuid;

		$field = $this->Users->getExposedKey();
		/** @var \TestApp\Model\Entity\User $result */
		$result = $this->Users->find('exposed', [$field => $uuid])->firstOrFail();

		$this->assertSame($user->id, $result->id);
	}

	/**
	 * @return void
	 */
	public function testFindExposedList() {
		$user = $this->Users->find()->firstOrFail();

		/** @var string[] $result */
		$result = $this->Users->find('exposedList')->toArray();

		$this->assertSame('Foo Bar', $result[$user->uuid]);
	}

	/**
	 * @return void
	 */
	public function testInitExposedField(): void {
		$customFieldRecordsTable = TableRegistry::getTableLocator()->get('Expose.CustomFieldRecords');

		$user = $customFieldRecordsTable->newEmptyEntity();
		$user->name = 'My Name';

		$user = $this->Users->saveOrFail($user);

		dd($user);
	}

}
