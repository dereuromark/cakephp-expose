<?php

namespace Expose\Converter;

use Keiko\Uuid\Shortener\Dictionary;
use Keiko\Uuid\Shortener\Number\BigInt\Converter;
use Keiko\Uuid\Shortener\Shortener;

/**
 * Make sure to include the required dependency `"keiko/uuid-shortener"`.
 *
 * @link https://github.com/mgrajcarek/uuid-shortener
 */
class KeikoShort implements ConverterInterface {

	/**
	 * @var \Keiko\Uuid\Shortener\Shortener
	 */
	protected Shortener $shortener;

	/**
	 * @param \Keiko\Uuid\Shortener\Dictionary|null $dictionary
	 */
	public function __construct(?Dictionary $dictionary = null) {
		$this->shortener = new Shortener(
			$dictionary ?: Dictionary::createUnmistakable(),
			new Converter(),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function encode(string $value): string {
		return $this->shortener->reduce($value);
	}

	/**
	 * @inheritDoc
	 */
	public function decode(string $value): string {
		return $this->shortener->expand($value);
	}

}
