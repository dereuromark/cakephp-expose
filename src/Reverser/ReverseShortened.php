<?php

namespace Expose\Reverser;

use Expose\Converter\ConverterFactory;
use RuntimeException;

class ReverseShortened implements ReverseStrategyInterface {

	/**
	 * @inheritDoc
	 */
	public function reverse(string $uuid): string {
		if (strlen($uuid) === 36) {
			throw new RuntimeException();
		}

		return ConverterFactory::getConverter()->decode($uuid);
	}

}
