<?php

namespace Expose\Reverser;

use Cake\Database\Driver\Mysql;
use Cake\Database\Type\BinaryUuidType;
use RuntimeException;

class ReverseHex implements ReverseStrategyInterface {

	/**
	 * @inheritDoc
	 */
	public function reverse(string $uuid): string {
		if (strlen($uuid) !== 34 || !str_starts_with($uuid, '0x')) {
			throw new RuntimeException('Expected hex format starting with "0x" and length 34, got: ' . $uuid);
		}

		$binaryUuid = @hex2bin(substr($uuid, 2));
		if ($binaryUuid === false || strlen($binaryUuid) !== 16) {
			throw new RuntimeException('Invalid hex content in UUID: ' . $uuid);
		}

		return (string)(new BinaryUuidType())->toPHP($binaryUuid, new Mysql());
	}

}
