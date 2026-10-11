<?php

namespace TestApp\Model\Behavior;

use Cake\Database\Query\SelectQuery;
use Expose\Model\Behavior\SuperimposeBehavior;

class TypedSuperimposeBehavior extends SuperimposeBehavior {

	public int $calls = 0;

	public array $resolved = [];

	public function findSuperimpose(SelectQuery $query): SelectQuery {
		$this->calls++;

		return parent::findSuperimpose($query);
	}

	/**
	 * @param string $uuid UUID
	 * @return string|int
	 */
	protected function resolve(string $uuid): string|int {
		$this->resolved[] = $uuid;

		return parent::resolve($uuid);
	}

}
