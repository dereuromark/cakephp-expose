<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use TestApp\Model\Entity\Post;
use TestApp\Model\Table\UsersTable;

class SuperimposeBehaviorTest extends TestCase {

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.Posts',
	];

	/**
	 * @var \TestApp\Model\Table\UsersTable
	 */
	protected UsersTable $Users;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Users = $this->getTableLocator()->get('Users');
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
	public function testSaveRelatedHasMany(): void {
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

		$user = $this->Users->get($user->id, contain: ['Posts']);

		$this->assertSame($uuid, $user->uuid);

		$this->assertCount(2, $user->posts);
		$this->assertNotEmpty($user->_id);
		$this->assertNotEmpty($user->posts[0]->_id);
		$this->assertNotEmpty($user->posts[1]->_id);

		$this->assertSame([], $user->getDirty());
	}

	/**
	 * @return void
	 */
	public function testSaveRelatedBelongsTo(): void {
		$this->Users->Posts->addBehavior('Expose.Superimpose');

		$post = $this->Users->Posts->newEntity([
			'content' => 'New User',
			'user' => [
				'name' => 'Me',
			],
		]);

		$this->Users->Posts->saveOrFail($post);

		$this->assertNotEmpty($post->uuid);
		$this->assertNotEmpty($post->_id);
		$this->assertSame($post->uuid, $post->id);

		$this->assertNotEmpty($post->user->uuid);
		$this->assertEmpty($post->user->_id);
	}

	/**
	 * @return void
	 */
	public function testSaveRelatedAllSuperimposedRecursiveOff(): void {
		$this->Users->Posts->addBehavior('Expose.Superimpose', ['recursive' => false]);
		$this->Users->addBehavior('Expose.Superimpose', ['recursive' => false]);

		$post = $this->Users->Posts->newEntity([
			'content' => 'New User',
			'user' => [
				'name' => 'Me',
			],
		]);

		$this->Users->Posts->saveOrFail($post);

		$this->assertNotEmpty($post->user->uuid);
		$this->assertEmpty($post->user->_id);
	}

	/**
	 * @return void
	 */
	public function testSaveRelatedAllSuperimposed(): void {
		$this->Users->Posts->addBehavior('Expose.Superimpose');
		$this->Users->addBehavior('Expose.Superimpose');

		$post = $this->Users->Posts->newEntity([
			'content' => 'New User',
			'user' => [
				'name' => 'Me',
			],
		]);

		$this->expectException(InvalidArgumentException::class);
		if (version_compare(Configure::version(), '5.0.3', '<')) {
			$this->expectExceptionMessage('Cannot convert value of type `string` to integer');
		} else {
			$this->expectExceptionMessageMatches('#Cannot convert value `.+` of type `string` to int#');
		}

		$this->Users->Posts->saveOrFail($post);
	}

}
