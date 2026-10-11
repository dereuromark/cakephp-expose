<?php

namespace Expose\Model\Behavior;

use ArrayObject;
use Cake\Collection\CollectionInterface;
use Cake\Database\Expression\ComparisonExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Query\SelectQuery;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

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
	protected array $_defaultConfig = [
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
		$pk = $this->_table->getPrimaryKey();
		$field = $this->_table->getBehavior('Expose')->getExposedKey();
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
		$pk = $this->_table->getPrimaryKey();
		$field = $this->_table->getBehavior('Expose')->getExposedKey();

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

		$query->traverseExpressions(function (object $expression): void {
			if (!$expression instanceof QueryExpression) {
				return;
			}
			$expression->iterateParts(function (mixed $part): mixed {
				/** @var string $pk */
				$pk = $this->_table->getPrimaryKey();
				if (!$part instanceof ComparisonExpression || !in_array($part->getField(), [$pk, $this->_table->aliasField($pk)], true)) {
					return $part;
				}
				if (is_array($part->getValue()) && in_array($part->getOperator(), ['IN', 'NOT IN'], true)) {
					$field = $this->_table->getBehavior('Expose')->getExposedKey();
					$type = $this->_table->getSchema()->getColumnType($field);

					return new ComparisonExpression($this->_table->aliasField($field), $part->getValue(), $type . '[]', $part->getOperator());
				}
				$part->setValue($this->resolve($part->getValue()));

				return $part;
			});
		});

		if (!$this->getConfig('autoFinder')) {
			return;
		}

		$query = $query->find('superimpose');

		if (!$this->getConfig('recursive')) {
			return;
		}

		foreach ($this->_table->associations() as $association) {
			/** @var \Cake\ORM\Association $association */
			if ($association->getTarget()->hasBehavior('Expose') && $association->getFinder() === 'all') {
				if (!$association->hasBehavior('Superimpose')) {
					$association->addBehavior('Expose.Superimpose');
				}
				$association->setFinder('superimpose');
			}
		}
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
				$pk = $this->_table->getPrimaryKey();
				$field = $this->_table->getBehavior('Expose')->getExposedKey();
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
		$pk = $this->_table->getPrimaryKey();
		$field = $this->_table->getBehavior('Expose')->getExposedKey();
		$alias = $this->getConfig('primaryKeyField');
		if (isset($entity->$field) && isset($entity->$alias)) {
			$entity->$pk = $entity->$alias;
			$entity->setDirty($pk, false);
		}
	}

	/**
	 * @param mixed $uuid UUID or comparison value to resolve
	 *
	 * @return mixed
	 */
	protected function resolve(mixed $uuid): mixed {
		if (is_array($uuid)) {
			return array_map($this->resolve(...), $uuid);
		}
		if (is_scalar($uuid)) {
			$uuid = (string)$uuid;
		}
		if (!is_string($uuid)) {
			return $uuid;
		}

		$primaryKey = $this->_table->getPrimaryKey();

		$record = $this->_table
			->find()
			->where([$this->_table->getBehavior('Expose')->getExposedKey() => $uuid])
			->select([$primaryKey])
			->firstOrFail();

		return $record->$primaryKey;
	}

}
