<?php

namespace Expose\Converter;

use Brick\Math\BigInteger;
use Brick\Math\RoundingMode;
use RuntimeException;

/**
 * Make sure to include the required dependency `"brick/math": "^0.8.14"`.
 *
 * @note Does not support UUID v7 at this point.
 */
class Short implements ConverterInterface {

	/**
	 * @var array<string>
	 */
	protected array $dictionary = [
		'2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
		'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
		'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n',
		'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
	];

	/**
	 * @var int
	 */
	protected int $dictionaryLength = 57;

	/**
	 * @param array<string>|null $dictionary
	 */
	public function __construct(?array $dictionary = null) {
		if ($dictionary !== null) {
			$this->dictionary = $dictionary;
			$this->dictionaryLength = count($dictionary);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function encode(string $value): string {
		$value = str_replace('-', '', $value);
		$uuidInteger = BigInteger::fromBase($value, 16);

		return $this->numToString($uuidInteger);
	}

	/**
	 * @inheritDoc
	 */
	public function decode(string $value): string {
		$uuidInteger = $this->stringToNum($value);
		$uuidInteger = BigInteger::fromBase($uuidInteger, 10)->toBase(16);

		return $this->formatHex($uuidInteger);
	}

	/**
	 * Transforms a given (big) number to a string value, based on the set dictionary.
	 *
	 * @param \Brick\Math\BigInteger $number
	 *
	 * @return string
	 */
	protected function numToString(BigInteger $number): string {
		$output = '';
		while ($number->isGreaterThan(0)) {
			$previousNumber = clone $number;
			$number = $number->dividedBy($this->dictionaryLength, RoundingMode::DOWN);
			$digit = $previousNumber->mod($this->dictionaryLength);

			$output .= $this->dictionary[(int)(string)$digit];
		}

		return $output;
	}

	/**
	 * Transforms a given string to a (big) number, based on the set dictionary.
	 *
	 * @param string $string
	 *
	 * @return \Brick\Math\BigInteger
	 */
	protected function stringToNum(string $string): BigInteger {
		$number = BigInteger::of(0);
		foreach (str_split(strrev($string)) as $char) {
			$plus = array_search($char, $this->dictionary, true);
			if ($plus === false) {
				throw new RuntimeException('Invalid char `' . $char . '`');
			}
			$number = $number->multipliedBy($this->dictionaryLength)->plus($plus);
		}

		return $number;
	}

	/**
	 * @param string $hex
	 *
	 * @return string
	 */
	protected function formatHex(string $hex): string {
		$hex = str_pad($hex, 32, '0');
		preg_match('/([a-f0-9]{8})([a-f0-9]{4})([a-f0-9]{4})([a-f0-9]{4})([a-f0-9]{12})/', $hex, $matches);
		array_shift($matches);

		return implode('-', $matches);
	}

}
