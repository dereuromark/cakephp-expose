<?php

namespace TestApp\Model\Behavior;

use Cake\Database\Query\SelectQuery;
use Expose\Model\Behavior\ExposeBehavior;

class LegacyExposeBehavior extends ExposeBehavior {

	public int $calls = 0;

	public array $resolved = [];

	public function findExposedList(SelectQuery $query, array $options): SelectQuery {
		$this->calls++;

		return parent::findExposedList($query, $options);
	}

}
