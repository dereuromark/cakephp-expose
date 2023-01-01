<?php

namespace Expose\Reverser;

use Exception;

class Reverser {

	/**
	 * @var array<string>
	 */
	protected $stategies = [
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
		foreach ($this->stategies as $stategy) {
			try {
				/** @var \Expose\Reverser\ReverseStrategyInterface $object */
				$object = new $stategy();

				return $object->reverse($uuid);
			} catch (Exception $exception) {
				// Do nothing
			}
		}

		return null;
	}

}
