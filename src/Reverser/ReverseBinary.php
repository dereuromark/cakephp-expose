<?php

namespace Expose\Reverser;

use Cake\Database\Driver\Mysql;
use Cake\Database\Type\BinaryUuidType;
use RuntimeException;

class ReverseBinary implements ReverseStrategyInterface {

	/**
	 * @inheritDoc
	 */
	public function reverse(string $uuid): string {
		if (strlen($uuid) !== 16) {
			throw new RuntimeException('Expected 16-byte binary UUID, got ' . strlen($uuid) . ' bytes');
		}

		return (string)(new BinaryUuidType())->toPHP($uuid, new Mysql());
	}

}
