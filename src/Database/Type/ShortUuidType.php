<?php

namespace Expose\Database\Type;

use Cake\Core\Exception\CakeException;
use Cake\Database\Driver;
use Cake\Database\Type\BinaryUuidType;
use Cake\Utility\Text;
use Expose\Converter\ConverterFactory;

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
	 * @param \Cake\Database\Driver $driver The driver instance to convert with.
	 * @return resource|string|null
	 */
	public function toDatabase(mixed $value, Driver $driver): mixed {
		if (is_string($value)) {
			if (strlen($value) !== 36) {
				$value = $this->lengthen($value);
			}

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
	 * @param \Cake\Database\Driver $driver The driver instance to convert with.
	 * @throws \Cake\Core\Exception\CakeException
	 * @return resource|string|null
	 */
	public function toPHP(mixed $value, Driver $driver): mixed {
		if ($value === null) {
			return null;
		}
		if (is_string($value)) {
			$value = $this->convertBinaryUuidToString($value);

			if (strlen($value) === 36) {
				$value = $this->shorten($value);
			}

			return $value;
		}
		if (is_resource($value)) {
			return $value;
		}

		throw new CakeException(sprintf('Unable to convert %s into binary uuid.', gettype($value)));
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function shorten(string $value): string {
		return ConverterFactory::getConverter()->encode($value);
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function lengthen(string $value): string {
		return ConverterFactory::getConverter()->decode($value);
	}

}
