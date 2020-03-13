<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class SuperimposeBehaviorTest extends TestCase {

	/**
	 * @var string[]
	 */
	protected $fixtures = [
		'plugin.Expose.Users',
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
	public function testSave(): void {
		$this->Users->addBehavior('Expose.Superimpose');

		$user = $this->Users->newEntity([
			'name' => 'New User',
		]);
		$this->assertNotEmpty($user->uuid);

		$this->Users->saveOrFail($user);

		$this->assertSame($user->id, $user->uuid);
		$this->assertNotEmpty($user->_id);

		$fetchedUser = $this->Users->get($user->id);
		$this->assertSame($user->uuid, $fetchedUser->uuid);
		$this->assertSame($user->uuid, $fetchedUser->id);
		$this->assertSame($user->_id, $fetchedUser->_id);
	}

}
