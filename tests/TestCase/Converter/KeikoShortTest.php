<?php

namespace Expose\Test\TestCase\Converter;

use Cake\TestSuite\TestCase;
use Expose\Converter\ConverterFactory;
use Expose\Converter\KeikoShort;
use Ramsey\Uuid\Uuid;

class KeikoShortTest extends TestCase {

	/**
	 * @var \Expose\Converter\KeikoShort
	 */
	protected $converter;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->converter = new KeikoShort();
	}

	/**
	 * @return void
	 */
	public function testEncode(): void {
		$uuid = '806d0969-95b3-433b-976f-774611fdacbb';
		$result = $this->converter->encode($uuid);

		$this->assertSame('mavTAjNm4NVztDwh4gdSrQ', $result);
	}

	/**
	 * @return void
	 */
	public function testDecode(): void {
		$shortId = 'mavTAjNm4NVztDwh4gdSrQ';
		$result = $this->converter->decode($shortId);

		$this->assertSame('806d0969-95b3-433b-976f-774611fdacbb', $result);
	}

	/**
	 * @return void
	 */
	public function testUuid6(): void {
		$this->skipIf(!method_exists(Uuid::class, 'uuid6'), 'Only PHP 8+');

		$uuidOrginal = Uuid::uuid6()->toString();
		$uuidShort = ConverterFactory::getConverter()->encode($uuidOrginal);
		$uuidDecoded = ConverterFactory::getConverter()->decode($uuidShort);
		$this->assertSame($uuidOrginal, $uuidDecoded);
	}

	/**
	 * @return void
	 */
	public function testUuid7(): void {
		$this->skipIf(!method_exists(Uuid::class, 'uuid6'), 'Only PHP 8+');

		$uuidOrginal = Uuid::uuid7()->toString();
		$uuidShort = ConverterFactory::getConverter()->encode($uuidOrginal);
		$uuidDecoded = ConverterFactory::getConverter()->decode($uuidShort);
		$this->assertSame($uuidOrginal, $uuidDecoded);
	}

}
