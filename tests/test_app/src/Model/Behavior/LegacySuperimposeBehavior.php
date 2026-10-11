<?php

namespace TestApp\Model\Behavior;

use Cake\Database\Query\SelectQuery;
use Expose\Model\Behavior\SuperimposeBehavior;

class LegacySuperimposeBehavior extends SuperimposeBehavior {

	public int $calls = 0;

	public array $resolved = [];

	public function findSuperimpose(SelectQuery $query, array $options): SelectQuery {
		$this->calls++;

		return parent::findSuperimpose($query, $options);
	}

	/**
	 * @param string $uuid UUID
	 * @return string|int
	 */
	protected function resolve(string $uuid) {
		$this->resolved[] = $uuid;

		return parent::resolve($uuid);
	}

}
