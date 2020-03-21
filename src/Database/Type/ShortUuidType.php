<?php

namespace Expose\Database\Type;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Database\DriverInterface;
use Cake\Database\Type\BinaryUuidType;
use Cake\Utility\Text;
use Expose\Converter\ConverterInterface;
use Expose\Converter\KeikoShort;

/**
 * Short Binary UUID type converter. Stored as binary16, displayed as char22.
 *
 * Use to convert binary uuid data between PHP and the database types.
 */
class ShortUuidType extends BinaryUuidType {

	/**
	 * Convert binary uuid data into the database format.
	 *
	 * Binary data is not altered before being inserted into the database.
	 * As PDO will handle reading file handles.
	 *
	 * @param mixed $value The value to convert.
	 * @param \Cake\Database\DriverInterface $driver The driver instance to convert with.
	 * @return string|resource
	 */
	public function toDatabase($value, DriverInterface $driver) {
		if (is_string($value)) {
			$value = $this->lengthen($value);

			return $this->convertStringToBinaryUuid($value);
		}

		return $value;
	}

	/**
	 * Generate a new short UUID.
	 *
	 * @return string A new primary key value.
	 */
	public function newId(): string {
		return $this->shorten(Text::uuid());
	}

	/**
	 * Convert short UUID into resource handles
	 *
	 * @param mixed $value The value to convert.
	 * @param \Cake\Database\DriverInterface $driver The driver instance to convert with.
	 * @return resource|string|null
	 * @throws \Cake\Core\Exception\Exception
	 */
	public function toPHP($value, DriverInterface $driver) {
		if ($value === null) {
			return null;
		}
		if (is_string($value)) {
			$value = $this->convertBinaryUuidToString($value);

			return $this->shorten($value);
		}
		if (is_resource($value)) {
			return $value;
		}

		throw new Exception(sprintf('Unable to convert %s into binary uuid.', gettype($value)));
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function shorten(string $value): string {
		return $this->converter()->encode($value);
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function lengthen(string $value): string {
		return $this->converter()->decode($value);
	}

	/**
	 * @return \Expose\Converter\ConverterInterface
	 */
	protected function converter(): ConverterInterface {
		$converter = Configure::read('Expose.converter');
		if ($converter !== null && is_callable($converter)) {
			return $converter();
		}

		if ($converter === null) {
			$converter = KeikoShort::class;
		}

		return new $converter();
	}

}
