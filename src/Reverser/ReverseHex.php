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
		if (strlen($uuid) === 34 && strpos($uuid, '0x') === 0) {
			$binaryUuid = hex2bin(substr($uuid, 2));

			return (string)(new BinaryUuidType())->toPHP($binaryUuid, new Mysql());
		}

		throw new RuntimeException('Expected hex format starting with "0x" and length 34, got: ' . $uuid);
	}

}
