<?php

namespace Expose\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
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

	public function testUuidListCondition(): void {
		$uuid = 'eb25610d-7bfa-4e34-812c-ad72b100fb26';
		$this->Users->addBehavior('Expose.Superimpose');

		$users = $this->Users->find()->where(['Users.id IN' => [$uuid]])->all()->toList();

		$this->assertCount(1, $users);
		$this->assertSame($uuid, $users[0]->id);
		$this->assertSame(1, $users[0]->_id);
	}

	public function testInternalIntegerKeyRemainsAvailableToOrmRules(): void {
		$this->Users->addBehavior('Expose.Superimpose');

		$this->assertTrue($this->Users->exists(['id' => 1]));
		$this->assertSame(1, $this->Users->get(1)->_id);
	}

	public function testNumericStringIsNotAnInternalKey(): void {
		$this->Users->addBehavior('Expose.Superimpose');

		$this->expectException(RecordNotFoundException::class);
		$this->Users->get('1');
	}

	public function testOtherTableSubqueryKeepsItsOwnPrimaryKey(): void {
		$posts = $this->getTableLocator()->get('Posts');
		$posts->saveOrFail($posts->newEntity(['user_id' => 1, 'content' => 'Example']));
		$this->Users->addBehavior('Expose.Superimpose');
		$subquery = $posts->find()->select(['user_id'])->where(['id IN' => [1]]);

		$this->assertSame(1, $this->Users->find()->where(['Users.id IN' => $subquery])->count());
	}

	public function testInvalidUuidListsDoNotReachTheDatabase(): void {
		$this->Users->addBehavior('Expose.Superimpose');

		$this->assertSame(0, $this->Users->find()->where(['Users.id IN' => ['invalid', 1]])->count());
		$this->assertSame(1, $this->Users->find()->where(['Users.id NOT IN' => ['invalid', 1]])->count());
	}

	public function testUuidListIgnoresUnknownIdentifiers(): void {
		$uuid = 'eb25610d-7bfa-4e34-812c-ad72b100fb26';
		$unknown = '806d0969-95b3-433b-976f-774611fdacbb';
		$this->Users->addBehavior('Expose.Superimpose');

		$users = $this->Users->find()->where(['Users.id IN' => [$uuid, $unknown]])->all()->toList();

		$this->assertCount(1, $users);
		$this->assertSame($uuid, $users[0]->id);
		$this->assertSame(0, $this->Users->find()->where(['Users.id IN' => [$unknown]])->count());
		$this->assertSame(1, $this->Users->find()->where(['Users.id NOT IN' => [$unknown]])->count());
		$this->assertSame(0, $this->Users->find()->where(['Users.id NOT IN' => [$uuid, $unknown]])->count());
	}

	public function testNullPrimaryKeyCondition(): void {
		$this->Users->addBehavior('Expose.Superimpose');

		$this->assertSame(1, $this->Users->find()->where(['Users.id IS NOT' => null])->count());
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
