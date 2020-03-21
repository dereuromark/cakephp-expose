<?php

namespace Expose\Test\TestCase\Database\Type;

use Cake\Core\Configure;
use Cake\Database\DriverInterface;
use Cake\TestSuite\TestCase;
use Expose\Converter\KeikoShort;
use Expose\Database\Type\ShortUuidType;
use Keiko\Uuid\Shortener\Dictionary;

class ShortUuidTypeTest extends TestCase {

	/**
	 * @var \Expose\Database\Type\ShortUuidType
	 */
	protected $type;

	/**
	 * @var \Cake\Database\DriverInterface|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected $driver;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->driver = $this->getMockBuilder(DriverInterface::class)->getMock();
		$this->type = new ShortUuidType();
	}

	/**
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();

		Configure::delete('Expose');
	}

	/**
	 * @return void
	 */
	public function testNewId(): void {
		$result = $this->type->newId();
		$this->assertTrue(strlen($result) === 22, $result);
	}

	/**
	 * @return void
	 */
	public function testToDatabase(): void {
		$shortId = 'mavTAjNm4NVztDwh4gdSrQ';
		$result = $this->type->toDatabase($shortId, $this->driver);

		$this->assertSame('806d096995b3433b976f774611fdacbb', bin2hex($result));
	}

	/**
	 * @return void
	 */
	public function testToPhp(): void {
		$binaryId = hex2bin('806d096995b3433b976f774611fdacbb');
		$result = $this->type->toPHP($binaryId, $this->driver);

		$this->assertSame('mavTAjNm4NVztDwh4gdSrQ', $result);
	}

	/**
	 * @return void
	 */
	public function testKeikoToPhp(): void {
		Configure::write('Expose.converter', KeikoShort::class);

		$binaryId = hex2bin('806d096995b3433b976f774611fdacbb');
		$result = $this->type->toPHP($binaryId, $this->driver);

		$this->assertSame('mavTAjNm4NVztDwh4gdSrQ', $result);
	}

	/**
	 * @return void
	 */
	public function testCallableNewId(): void {
		Configure::write('Expose.converter', $this->callable());

		$result = $this->type->newId();
		$this->assertTrue(strlen($result) === 22, $result);
	}

	/**
	 * @return void
	 */
	public function testCallableToDatabase(): void {
		Configure::write('Expose.converter', $this->callable());

		$shortId = 'rfMuQ8HQ7CY0sc9avYqKu4';
		$result = $this->type->toDatabase($shortId, $this->driver);

		$this->assertSame('806d096995b3433b976f774611fdacbb', bin2hex($result));
	}
	/**
	 * @return void
	 */
	public function testCallableToPhp(): void {
		Configure::write('Expose.converter', $this->callable());

		$binaryId = hex2bin('806d096995b3433b976f774611fdacbb');
		$result = $this->type->toPHP($binaryId, $this->driver);

		$this->assertSame('rfMuQ8HQ7CY0sc9avYqKu4', $result);
	}

	/**
	 * @return callable
	 */
	protected function callable(): callable {
		return function () {
			return new KeikoShort(Dictionary::createAlphanumeric());
		};
	}

}
