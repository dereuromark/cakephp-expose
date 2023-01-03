<?php

namespace Expose\Test\TestCase\Converter;

use Cake\TestSuite\TestCase;
use Expose\Converter\Short;

class ShortTest extends TestCase {

	/**
	 * @var \Expose\Converter\Short
	 */
	protected Short $converter;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->converter = new Short();
	}

	/**
	 * @return void
	 */
	public function testEncode(): void {
		$uuid = '4e52c919-513e-4562-9248-7dd612c6c1ca';
		$result = $this->converter->encode($uuid);

		$this->assertSame('fpfyRTmt6XeE9ehEKZ5LwF', $result);
	}

	/**
	 * @return void
	 */
	public function testDecode(): void {
		$shortId = 'fpfyRTmt6XeE9ehEKZ5LwF';
		$result = $this->converter->decode($shortId);

		$this->assertSame('4e52c919-513e-4562-9248-7dd612c6c1ca', $result);
	}

}
