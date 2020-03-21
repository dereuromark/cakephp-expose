<?php

namespace Expose\Converter;

interface ConverterInterface {

	/**
	 * UUID to ShortUUID
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function encode(string $value): string;

	/**
	 * Back from ShortUUID to UUID
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function decode(string $value): string;

}
