<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class ExposeBehaviorTest extends TestCase {

	/**
	 * @var string[]
	 */
	protected $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.CustomFieldRecords',
		'plugin.Expose.ExistingRecords',
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

		$this->Users = TableRegistry::getTableLocator()->get('Users');
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
	public function testCustomExposedField(): void {
		$customFieldRecordsTable = TableRegistry::getTableLocator()->get('CustomFieldRecords');

		$record = $customFieldRecordsTable->newEntity([
			'name' => 'New User',
		]);
		$this->assertNotEmpty($record->public_identifier);

		$customFieldRecordsTable->saveOrFail($record);

		$record = $customFieldRecordsTable->get($record->id);

		$this->assertNotEmpty($record->public_identifier);
	}

	/**
	 * @return void
	 */
	public function testCustomExposedFieldBeforeSave(): void {
		$customFieldRecordsTable = TableRegistry::getTableLocator()->get('CustomFieldRecords');

		$config = ['on' => 'beforeSave'] + $customFieldRecordsTable->behaviors()->Expose->getConfig();
		$customFieldRecordsTable->removeBehavior('Expose');
		$customFieldRecordsTable->addBehavior('Expose.Expose', $config);

		$user = $customFieldRecordsTable->newEntity([
			'name' => 'New User',
		]);
		$this->assertEmpty($user->public_identifier);

		$customFieldRecordsTable->saveOrFail($user);

		$this->assertNotEmpty($user->public_identifier);
	}

	/**
	 * @return void
	 */
	public function testInitExposedField(): void {
		$customFieldRecordsTable = TableRegistry::getTableLocator()->get('ExistingRecords');

		/** @var \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $customFieldRecordsTable */
		$customFieldRecordsTable->addBehavior('Expose.Expose');

		$count = $customFieldRecordsTable->initExposedField();
		$this->assertSame(1, $count);
	}

}
