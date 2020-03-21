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
			throw new RuntimeException();
		}

		return (new BinaryUuidType())->toPHP($uuid, new Mysql());
	}

}
