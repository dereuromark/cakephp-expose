<?php

namespace Expose\Test\TestCase\Converter;

use Cake\TestSuite\TestCase;
use Expose\Converter\Short;
use Ramsey\Uuid\Uuid;

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

	/**
	 * @return void
	 */
	public function testUuid6(): void {
		$this->skipIf(!method_exists(Uuid::class, 'uuid6'), 'Only PHP 8+');

		$uuidOriginal = Uuid::uuid6()->toString();
		$uuidShort = $this->converter->encode($uuidOriginal);
		$uuidDecoded = $this->converter->decode($uuidShort);
		$this->assertSame($uuidOriginal, $uuidDecoded);
	}

	/**
	 * @return void
	 */
	public function testUuid7(): void {
		$this->skipIf(!method_exists(Uuid::class, 'uuid7'), 'Only ramsey/uuid 4.7+');

		$uuidOriginal = Uuid::uuid7()->toString();
		$uuidShort = $this->converter->encode($uuidOriginal);
		$uuidDecoded = $this->converter->decode($uuidShort);
		$this->assertSame($uuidOriginal, $uuidDecoded);
	}

	/**
	 * Regression: hex values with leading zero nibbles must round-trip.
	 * BigInteger drops most-significant zeros, so decode must left-pad to 32 chars.
	 *
	 * @return void
	 */
	public function testEncodeDecodeWithLeadingZero(): void {
		$uuid = '0e52c919-513e-4562-9248-7dd612c6c1ca';
		$encoded = $this->converter->encode($uuid);
		$decoded = $this->converter->decode($encoded);
		$this->assertSame($uuid, $decoded);
	}

}
