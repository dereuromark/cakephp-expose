<?php

namespace Expose\Reverser;

interface ReverseStrategyInterface {

	/**
	 * @param string $uuid To try to reverse
	 *
	 * @throws \RuntimeException If it cannot reverse.
	 *
	 * @return string UUID if successfully reversed.
	 */
	public function reverse(string $uuid): string;

}
