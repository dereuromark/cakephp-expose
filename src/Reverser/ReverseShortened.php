<?php

namespace Expose\Reverser;

use Cake\Core\Configure;
use Expose\Converter\ConverterInterface;
use Expose\Converter\Short;
use RuntimeException;

class ReverseShortened implements ReverseStrategyInterface {

	/**
	 * @inheritDoc
	 */
	public function reverse(string $uuid): string {
		if (strlen($uuid) === 36) {
			throw new RuntimeException();
		}

		return $this->converter()->decode($uuid);
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
			$converter = Short::class;
		}

		return new $converter();
	}

}
