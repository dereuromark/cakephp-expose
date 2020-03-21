<?php

namespace TestApp\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ExposeControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Expose', 'controller' => 'Expose', 'action' => 'index']);

		$this->assertResponseCode(200);
	}

}
