<?php

namespace Expose\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Expose\Controller\Component\SuperimposeComponent;
use TestApp\Controller\UsersController;

class SuperimposeComponentTest extends TestCase {

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.Posts',
	];

	/**
	 * @var \Cake\Controller\Controller
	 */
	protected $controller;

	/**
	 * @var \Expose\Controller\Component\SuperimposeComponent
	 */
	protected $component;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Users = TableRegistry::getTableLocator()->get('Users');

		$this->controller = new UsersController();
		$this->component = new SuperimposeComponent(new ComponentRegistry($this->controller));
	}

	/**
	 * @return void
	 */
	public function testBeforeFilter(): void {
		$event = new Event('event');
		$this->component->beforeFilter($event);

		$this->assertTrue($this->controller->loadModel()->hasBehavior('Superimpose'));
	}

	/**
	 * @return void
	 */
	public function testBeforeFilterActionBlacklisted(): void {
		$event = new Event('event');

		$this->component->setConfig('actions', ['view']);
		$this->controller->setRequest($this->controller->getRequest()->withParam('action', 'index'));

		$this->component->beforeFilter($event);

		$this->assertFalse($this->controller->loadModel()->hasBehavior('Superimpose'));
	}

}
