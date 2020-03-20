<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\Post;

class SuperimposeBehaviorTest extends TestCase {

	/**
	 * @var string[]
	 */
	protected $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.Posts',
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

		$this->Users->saveOrFail($user);
		$this->assertNotEmpty($user->uuid);

		$this->assertSame($user->id, $user->uuid);
		$this->assertNotEmpty($user->_id);

		$fetchedUser = $this->Users->get($user->id);
		$this->assertSame($user->uuid, $fetchedUser->uuid);
		$this->assertSame($user->uuid, $fetchedUser->id);
		$this->assertSame($user->_id, $fetchedUser->_id);
	}

	/**
	 * @return void
	 */
	public function testSaveRelated(): void {
		$this->Users->addBehavior('Expose.Superimpose');

		$user = $this->Users->newEntity([
			'name' => 'New User',
			'posts' => [
				[
					'content' => 'My post!',
				],
			],
		]);

		$this->Users->saveOrFail($user);

		$this->assertNotEmpty($user->uuid);
		$uuid = $user->uuid;

		$this->assertInstanceOf(Post::class, $user->posts[0]);
		$this->assertNotEmpty(Post::class, $user->posts[0]->uuid);

		$user = $this->Users->patchEntity($user, [
			'name' => 'New User',
			'posts' => [
				[
					'content' => 'My updated post!',
				],
			],
		]);

		$this->Users->saveOrFail($user);

		$this->assertSame([], $user->getDirty());

		$user = $this->Users->get($user->id, ['contain' => ['Posts']]);

		$this->assertSame($uuid, $user->uuid);

		$this->assertCount(2, $user->posts);
		$this->assertNotEmpty($user->_id);
		$this->assertNotEmpty($user->posts[0]->_id);
		$this->assertNotEmpty($user->posts[1]->_id);

		$this->assertSame([], $user->getDirty());
	}

}
