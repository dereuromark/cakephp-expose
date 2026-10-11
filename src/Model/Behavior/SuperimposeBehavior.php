<?php

namespace Expose\Model\Behavior;

use ArrayObject;
use Cake\Collection\CollectionInterface;
use Cake\Database\Expression\ComparisonExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\Query;
use Cake\Database\Query\SelectQuery;
use Cake\Database\Type\BinaryUuidType;
use Cake\Database\Type\UuidType;
use Cake\Database\TypeFactory;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Expose\Converter\ConverterFactory;
use Expose\Database\Type\ShortUuidType;
use InvalidArgumentException;
use RuntimeException;
use Stringable;
use WeakMap;

/**
 * Adds superimpose functionality on top of Expose behavior.
 * This should only be added at runtime to the specific actions where needed through SuperimposeComponent.
 *
 * @property \Cake\ORM\Table<array{Expose: \Expose\Model\Behavior\ExposeBehavior}> $_table
 */
class SuperimposeBehavior extends Behavior {

	/**
	 * @var array<string, mixed>
	 */
	protected array $defaultConfig = [
		'autoFinder' => true,
		'recursive' => true,
		'primaryKeyField' => '_id',
		'implementedFinders' => [
			'superimpose' => 'findSuperimpose',
		],
	];

	/**
	 * Switching UUID IDs with their AIID counterpart if found.
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		if ($entity->isNew()) {
			return;
		}

		/** @var string $pk */
		$pk = $this->table()->getPrimaryKey();
		$field = $this->exposeBehavior()->getExposedKey();
		$alias = $this->getConfig('primaryKeyField');
		if (isset($entity->$field) && isset($entity->$alias)) {
			$entity->$pk = $entity->$alias;
			$entity->setDirty($pk, false);
		}
	}

	/**
	 * Callback to superimpose the records' primary key returned after a save operation.
	 *
	 * @param \Cake\Event\EventInterface $event Event.
	 * @param \Cake\Datasource\EntityInterface $entity Entity.
	 * @param \ArrayObject $options Options.
	 * @return void
	 */
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		/** @var string $pk */
		$pk = $this->table()->getPrimaryKey();
		$field = $this->exposeBehavior()->getExposedKey();

		if (!$options['_primary'] && !$this->getConfig('recursive')) {
			return;
		}

		$alias = $this->getConfig('primaryKeyField');
		$entity->set($alias, $entity->$pk);
		$entity->setDirty($alias, false);

		$entity->set($pk, $entity->$field);
		$entity->setDirty($pk, false);
	}

	/**
	 * Callback to modify the primary key value and set the `superimposed` finder on all associations.
	 *
	 * @param \Cake\Event\EventInterface $event Event.
	 * @param \Cake\ORM\Query\SelectQuery $query Query.
	 * @param \ArrayObject $options Options.
	 * @param bool $primary True if this is the primary table.
	 * @return void
	 */
	public function beforeFind(EventInterface $event, SelectQuery $query, ArrayObject $options, bool $primary): void {
		if ((isset($options['superimpose']) && $options['superimpose'] === false) || !$primary) {
			return;
		}

		foreach (['where', 'having'] as $clause) {
			$expression = $query->clause($clause);
			if ($expression instanceof QueryExpression) {
				$this->rewriteConditions($expression);
			}
		}

		if (!$this->getConfig('autoFinder')) {
			return;
		}

		$query = $query->find('superimpose');

		if (!$this->getConfig('recursive')) {
			return;
		}

		foreach ($this->table()->associations() as $association) {
			/** @var \Cake\ORM\Association $association */
			if ($association->getTarget()->hasBehavior('Expose') && $association->getFinder() === 'all') {
				if (!$association->hasBehavior('Superimpose')) {
					$association->addBehavior('Expose.Superimpose');
				}
				$association->setFinder('superimpose');
			}
		}
	}

	protected function rewriteConditions(QueryExpression $expression): void {
		$expression->iterateParts(function (mixed $part): mixed {
			if ($part instanceof QueryExpression) {
				$this->rewriteConditions($part);

				return $part;
			}
			if ($part instanceof ExpressionInterface && !$part instanceof ComparisonExpression && !$part instanceof Query) {
				$ignored = new WeakMap();
				$part->traverse(function (mixed $nested) use ($ignored): void {
					if ($nested instanceof Query) {
						$nested->traverseExpressions(static function (ExpressionInterface $child) use ($ignored): void {
							$ignored[$child] = true;
						});
					} elseif ($nested instanceof QueryExpression && !isset($ignored[$nested])) {
						$this->rewriteConditions($nested);
					}
				});

				return $part;
			}
			/** @var string $pk */
			$pk = $this->table()->getPrimaryKey();
			if (!$part instanceof ComparisonExpression || !in_array($part->getField(), [$pk, $this->table()->aliasField($pk)], true)) {
				return $part;
			}
			if (is_array($part->getValue()) && in_array($part->getOperator(), ['IN', 'NOT IN'], true)) {
				$field = $this->exposeBehavior()->getExposedKey();
				$type = $this->table()->getSchema()->getColumnType($field);
				$integers = array_values(array_filter($part->getValue(), is_int(...)));
				$values = array_values(array_filter($part->getValue(), $this->isValidUuid(...)));
				if ($integers) {
					$operator = $part->getOperator();
					$conditions = new QueryExpression([], [], $operator === 'IN' ? 'OR' : 'AND');
					$conditions->add(new ComparisonExpression($this->table()->aliasField($pk), $integers, 'integer[]', $operator));
					if ($values) {
						$conditions->add(new ComparisonExpression($this->table()->aliasField($field), $values, $type . '[]', $operator));
					}

					return $conditions;
				}
				if (!$values) {
					return new QueryExpression($part->getOperator() === 'IN' ? '1 = 0' : '1 = 1');
				}

				return new ComparisonExpression($this->table()->aliasField($field), $values, $type . '[]', $part->getOperator());
			}
			$part->setValue($this->resolveComparisonValue($part->getValue()));

			return $part;
		});
	}

	protected function isValidUuid(mixed $value): bool {
		if (!is_string($value)) {
			return false;
		}
		$field = $this->exposeBehavior()->getExposedKey();
		$columnType = $this->table()->getSchema()->getColumnType($field);
		if ($columnType === null) {
			throw new RuntimeException('Exposed field is missing from the table schema: ' . $field);
		}
		$type = TypeFactory::build($columnType);
		if ($type instanceof ShortUuidType && strlen($value) !== 36) {
			try {
				return (bool)preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/iD', ConverterFactory::getConverter()->decode($value));
			} catch (InvalidArgumentException | RuntimeException) {
				return false;
			}
		}
		if ($type instanceof BinaryUuidType && strlen($value) === 32) {
			return ctype_xdigit($value);
		}
		if ($type instanceof UuidType || $type instanceof BinaryUuidType) {
			return (bool)preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/iD', $value);
		}

		return true;
	}

	/**
	 * Custom finder that superimposes the primary key with the UUID in returned result set.
	 *
	 * @param \Cake\ORM\Query\SelectQuery $query Query.
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findSuperimpose(SelectQuery $query): SelectQuery {
		$query->formatResults(function (CollectionInterface $results) {
			return $results->map(function ($row) {
				/** @var string $pk */
				$pk = $this->table()->getPrimaryKey();
				$field = $this->exposeBehavior()->getExposedKey();
				if (!isset($row[$field])) {
					return $row;
				}

				$alias = $this->getConfig('primaryKeyField');
				$row[$alias] = $row[$pk] ?? null;
				$row[$pk] = $row[$field];

				if ($row instanceof EntityInterface) {
					$row->setDirty($alias, false);
					$row->setDirty($pk, false);
				}

				return $row;
			});
		});

		return $query;
	}

	/**
	 * Switching UUID IDs with their AIID counterpart if found.
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @return void
	 */
	public function beforeDelete(EventInterface $event, EntityInterface $entity): void {
		/** @var string $pk */
		$pk = $this->table()->getPrimaryKey();
		$field = $this->exposeBehavior()->getExposedKey();
		$alias = $this->getConfig('primaryKeyField');
		if (isset($entity->$field) && isset($entity->$alias)) {
			$entity->$pk = $entity->$alias;
			$entity->setDirty($pk, false);
		}
	}

	/**
	 * @param mixed $value Comparison value
	 * @return mixed
	 */
	private function resolveComparisonValue(mixed $value): mixed {
		if (is_array($value)) {
			return array_map($this->resolveComparisonValue(...), $value);
		}

		if ($value instanceof ExpressionInterface) {
			return $value;
		}

		return is_string($value) || $value instanceof Stringable ? $this->resolve((string)$value) : $value;
	}

	/**
	 * @param string $uuid UUID to resolve
	 * @return string|int
	 */
	protected function resolve(string $uuid): string|int {
		if (!$this->isValidUuid($uuid)) {
			throw new RecordNotFoundException('Invalid exposed identifier.');
		}

		$primaryKey = $this->table()->getPrimaryKey();

		$record = $this->table()
			->find()
			->where([$this->exposeBehavior()->getExposedKey() => $uuid])
			->select([$primaryKey])
			->firstOrFail();

		return $record->$primaryKey;
	}

	/**
	 * @return \Expose\Model\Behavior\ExposeBehavior
	 */
	private function exposeBehavior(): ExposeBehavior {
		$behavior = $this->table()->getBehavior('Expose');
		if (!$behavior instanceof ExposeBehavior) {
			throw new RuntimeException('The Expose behavior must extend ExposeBehavior.');
		}

		return $behavior;
	}

}
