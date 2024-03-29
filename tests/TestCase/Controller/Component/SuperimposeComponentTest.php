<?php

namespace Expose\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Expose\Controller\Component\SuperimposeComponent;
use TestApp\Controller\UsersController;
use TestApp\Model\Table\UsersTable;

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
	protected Controller $controller;

	/**
	 * @var \TestApp\Model\Table\UsersTable
	 */
	protected UsersTable $Users;

	/**
	 * @var \Expose\Controller\Component\SuperimposeComponent
	 */
	protected SuperimposeComponent $component;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->controller = new UsersController(new ServerRequest());
		$this->component = new SuperimposeComponent(new ComponentRegistry($this->controller));
	}

	/**
	 * @return void
	 */
	public function testBeforeFilter(): void {
		$event = new Event('event');
		$this->component->beforeFilter($event);

		$this->assertTrue($this->controller->fetchTable()->hasBehavior('Superimpose'));
	}

	/**
	 * @return void
	 */
	public function testBeforeFilterActionBlacklisted(): void {
		$event = new Event('event');

		$this->component->setConfig('actions', ['view']);
		$this->controller->setRequest($this->controller->getRequest()->withParam('action', 'index'));

		$this->component->beforeFilter($event);

		$this->assertFalse($this->controller->fetchTable()->hasBehavior('Superimpose'));
	}

}
