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
		'plugin.Expose.BinaryFieldRecords',
	];

	/**
	 * @var \TestApp\Model\Table\UsersTable
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
	public function testBeforeSave(): void {
		$user = $this->Users->newEntity([
			'name' => 'New User',
		]);
		$this->assertEmpty($user->uuid);

		$this->Users->saveOrFail($user);

		$this->assertNotEmpty($user->uuid);
		$uuid = $user->uuid;

		$updatedUser = $this->Users->patchEntity($user, [
			'name' => 'Updated User',
		]);
		$this->Users->saveOrFail($updatedUser);

		$fetchedUser = $this->Users->get($updatedUser->id);
		$this->assertSame($uuid, $fetchedUser->uuid);
	}

	/**
	 * @return void
	 */
	public function testPatchAndSave(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['on' => 'beforeMarshal']);

		$user = $this->Users->newEntity([
			'name' => 'New User',
		]);
		$this->assertNotEmpty($user->uuid);
		$uuid = $user->uuid;

		$this->Users->saveOrFail($user);

		$user = $this->Users->get($user->id);

		$this->assertNotEmpty($user->uuid);

		$updatedUser = $this->Users->patchEntity($user, [
			'name' => 'Updated User',
		]);

		$this->Users->saveOrFail($updatedUser);

		$this->assertNotSame($uuid, $updatedUser->uuid);
	}

	/**
	 * @return void
	 */
	public function testMarshalFieldsConfig(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['on' => 'beforeMarshal']);

		$user = $this->Users->newEntity([
			'name' => 'New User',
			'created' => '2020-01-01',
		], ['fields' => ['name']]);
		$this->assertNotEmpty($user->uuid);

		$this->assertNull($user->created);
		$this->assertNull($user->modified);
		$this->assertNotEmpty($user->name);
	}

	/**
	 * @return void
	 */
	public function testFindExposed(): void {
		$user = $this->Users->find()->firstOrFail();

		$uuid = $user->uuid;

		$field = $this->Users->getExposedKey();
		$this->assertSame('uuid', $field);

		/** @var \TestApp\Model\Entity\User $result */
		$result = $this->Users->find('exposed', [$field => $uuid])->firstOrFail();

		$this->assertSame($user->id, $result->id);

		$field = $this->Users->getExposedKey(true);
		$this->assertSame('Users.uuid', $field);
	}

	/**
	 * @return void
	 */
	public function testFindExposedList(): void {
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
	public function testCustomExposedFieldBeforeMarshal(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['on' => 'beforeMarshal']);

		$customFieldRecordsTable = TableRegistry::getTableLocator()->get('CustomFieldRecords');

		$record = $customFieldRecordsTable->newEntity([
			'name' => 'New User',
		]);

		$customFieldRecordsTable->saveOrFail($record);

		$this->assertNotEmpty($record->public_identifier);

		$record = $customFieldRecordsTable->get($record->id);

		$this->assertNotEmpty($record->public_identifier);
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

		$records = $customFieldRecordsTable->find()->find('list', ['valueField' => 'uuid'])->toArray();
		foreach ($records as $id => $uuid) {
			$this->assertNotEmpty($uuid, 'ID ' . $id . ' not expected to have empty uuid field');
		}
	}

	/**
	 * @return void
	 */
	public function testBinaryUuidField(): void {
		$binaryFieldRecordsTable = TableRegistry::getTableLocator()->get('BinaryFieldRecords');

		/** @var \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $binaryFieldRecordsTable */
		$binaryFieldRecordsTable->addBehavior('Expose.Expose');
		$binaryFieldRecordsTable->addBehavior('Timestamp');

		$user = $binaryFieldRecordsTable->newEntity([
			'name' => 'New User',
		]);

		$binaryFieldRecordsTable->saveOrFail($user);

		$this->assertNotEmpty($user->uuid);

		$result = $binaryFieldRecordsTable->find('exposed', ['uuid' => $user->uuid])->firstOrFail();
		$this->assertSame($user->name, $result->name);
	}

}
