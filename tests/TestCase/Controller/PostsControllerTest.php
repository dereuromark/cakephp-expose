<?php

namespace TestApp\Test\TestCase\Controller;

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PostsControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.Posts',
	];

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		Router::defaultRouteClass(DashedRoute::class);
		Router::scope('/', function(RouteBuilder $routes): void {
			$routes->fallbacks();
		});
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd(): void {
		$this->disableErrorHandlerMiddleware();

		/** @var \TestApp\Model\Table\PostsTable $postsTable */
		$postsTable = $this->getTableLocator()->get('Posts');
		$postsTable->deleteAll('1=1');

		$data = [
			'content' => 'Foo Bar Baz',
			'user' => [
				'name' => 'me',
			],
		];
		$this->post(['controller' => 'Posts', 'action' => 'add'], $data);

		$this->assertRedirect();

		$postsTable->removeBehavior('Superimpose');
		/** @var \TestApp\Model\Entity\Post $post */
		$post = $postsTable->find()->contain(['Users'])->firstOrFail();
		$this->assertSame($data['content'], $post->content);
		$this->assertSame($data['user']['name'], $post->user->name);
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit(): void {
		$this->disableErrorHandlerMiddleware();

		$postsTable = $this->getTableLocator()->get('Posts');

		$post = $postsTable->find()->firstOrFail();
		$uuid = $post->uuid;

		$data = [
			'content' => 'Foo Bar Baz',
			'user_id' => 2,
		];
		$this->post(['controller' => 'Posts', 'action' => 'edit', $uuid], $data);

		$this->assertRedirect();

		$postsTable->removeBehavior('Superimpose');
		$post = $postsTable->get($post->id, ['contain' => ['Users']]);
		$this->assertSame($data['content'], $post->content);
	}

	/**
	 * @return void
	 */
	public function testDelete(): void {
		$this->disableErrorHandlerMiddleware();

		$postsTable = $this->getTableLocator()->get('Posts');

		$post = $postsTable->find()->firstOrFail();
		$uuid = $post->uuid;

		$this->post(['controller' => 'Posts', 'action' => 'delete', $uuid]);

		$this->assertRedirect();

		$postsTable->removeBehavior('Superimpose');
		$count = $postsTable->find()->where(['id' => $post->id])->all()->count();
		$this->assertSame(0, $count);
	}

}
