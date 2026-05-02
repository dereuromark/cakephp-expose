<?php

namespace Expose\Reverser;

use RuntimeException;

class Reverser {

	/**
	 * @var array<class-string<\Expose\Reverser\ReverseStrategyInterface>>
	 */
	protected array $strategies = [
		ReverseBinary::class,
		ReverseHex::class,
		ReverseShortened::class,
	];

	/**
	 * @param string $uuid
	 *
	 * @return string|null
	 */
	public function reverse(string $uuid): ?string {
		foreach ($this->strategies as $strategy) {
			try {
				return (new $strategy())->reverse($uuid);
			} catch (RuntimeException $exception) {
				// Strategy could not handle this input format, try next.
			}
		}

		return null;
	}

}
