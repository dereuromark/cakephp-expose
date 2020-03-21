<?php

namespace Expose\Test\TestCase\Reverser;

use Cake\TestSuite\TestCase;
use Expose\Reverser\Reverser;

class ReverserTest extends TestCase {

	/**
	 * @var \Expose\Reverser\Reverser
	 */
	protected $reverser;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->reverser = new Reverser();
	}

	/**
	 * @return void
	 */
	public function testReverseHex(): void {
		$uuid = '0xf7ac0123a9384e80840eefe892051332';
		$result = $this->reverser->reverse($uuid);

		$this->assertSame('f7ac0123-a938-4e80-840e-efe892051332', $result);
	}

	/**
	 * @return void
	 */
	public function testReverseShortened(): void {
		$shortUuid = 'fpfyRTmt6XeE9ehEKZ5LwF';
		$result = $this->reverser->reverse($shortUuid);

		$this->assertSame('4e52c919-513e-4562-9248-7dd612c6c1ca', $result);
	}

	/**
	 * @return void
	 */
	public function testReverseBinary(): void {
		$binaryUuid = file_get_contents(TESTS . 'test_files' . DS . 'uuid.bin');
		$result = $this->reverser->reverse($binaryUuid);

		$this->assertSame('f7ac0123-a938-4e80-840e-efe892051332', $result);
	}

}
