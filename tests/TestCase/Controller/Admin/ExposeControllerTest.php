<?php

namespace Expose\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\UploadedFile;

class ExposeControllerTest extends TestCase {

	use IntegrationTestTrait;

	/**
	 * @return void
	 */
	public function testIndex(): void {
		$this->disableErrorHandlerMiddleware();

		$this->get(['prefix' => 'Admin', 'plugin' => 'Expose', 'controller' => 'Expose', 'action' => 'index']);

		$this->assertResponseCode(200);
	}

	/**
	 * @return void
	 */
	public function testIndexReverse(): void {
		$this->disableErrorHandlerMiddleware();

		$data = [
			'uuid' => 'JG2n2fdRHcdMSiyDq5em5n',
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Expose', 'controller' => 'Expose', 'action' => 'index'], $data);

		$this->assertResponseCode(200);
	}

	/**
	 * @return void
	 */
	public function testIndexReverseFile(): void {
		$this->disableErrorHandlerMiddleware();

		$data = [
			'file' => new UploadedFile(TESTS . 'test_files' . DS . 'uuid.bin', 1, UPLOAD_ERR_OK),
		];
		$this->post(['prefix' => 'Admin', 'plugin' => 'Expose', 'controller' => 'Expose', 'action' => 'index'], $data);

		$this->assertResponseCode(200);
	}

}
