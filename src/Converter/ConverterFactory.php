<?php

namespace Expose\Converter;

use Cake\Core\Configure;

/**
 * Converter factory convenience wrapper.
 *
 * Can also be used inside your application as
 *   $result = ConverterFactory::getConverter()->encode($value)
 *   $result = ConverterFactory::getConverter()->decode($value)
 * based on your current config.
 */
class ConverterFactory {

	private function __construct() {
	}

	/**
	 * @return \Expose\Converter\ConverterInterface
	 */
	public static function getConverter(): ConverterInterface {
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
