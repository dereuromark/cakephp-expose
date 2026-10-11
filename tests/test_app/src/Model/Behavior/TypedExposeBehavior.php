<?php

namespace TestApp\Model\Behavior;

use Cake\Database\Query\SelectQuery;
use Closure;
use Expose\Model\Behavior\ExposeBehavior;

class TypedExposeBehavior extends ExposeBehavior {

	public int $calls = 0;

	public array $resolved = [];

	public function findExposedList(SelectQuery $query, Closure|array|string|null $keyField = null, Closure|array|string|null $valueField = null, Closure|array|string|null $groupField = null, string $valueSeparator = ' '): SelectQuery {
		$this->calls++;

		return parent::findExposedList($query, $keyField, $valueField, $groupField, $valueSeparator);
	}

}
