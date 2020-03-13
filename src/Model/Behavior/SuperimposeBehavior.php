<?php

namespace Expose\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;

/**
 * Adds superimpose functionality on top of Expose behavior.
 * This should only be added at runtime to the specific actions where needed through SuperimposeComponent.
 *
 * @property \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $_table
 */
class SuperimposeBehavior extends Behavior {

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'autoFinder' => true,
		'primaryKeyField' => '_id',
		'implementedFinders' => [
			'superimpose' => 'findSuperimpose',
		],
	];

	/**
	 * Callback to superimpose the records' primary key returned after a save operation.
	 *
	 * @param \Cake\Event\EventInterface $event Event.
	 * @param \Cake\Datasource\EntityInterface $entity Entity.
	 * @param \ArrayObject $options Options.
	 * @return void
	 */
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		$pk = $this->_table->getPrimaryKey();
		$field = $this->_table->getExposedKey();

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
	 * @param \Cake\ORM\Query $query Query.
	 * @param \ArrayObject $options Options.
	 * @param bool $primary True if this is the primary table.
	 * @return void
	 */
	public function beforeFind(EventInterface $event, Query $query, ArrayObject $options, bool $primary): void {
		if ((isset($options['superimpose']) && $options['superimpose'] === false) || !$primary) {
			return;
		}

		$query->traverseExpressions(function ($expression) {
			$pk = $this->_table->getPrimaryKey();
			if (
				method_exists($expression, 'getField')
				&& in_array($expression->getField(), [$pk, $this->_table->aliasField($pk)], true)
			) {
				/** @var \Cake\Database\Expression\Comparison $expression */
				//$field = $this->_table->getExposedKey(); // This doesnt work yet, so we have to make an extra query
				//$expression = new Comparison($field, $expression->getValue(), 'string');
				$expression->setValue($this->resolve($expression->getValue()));

				return $expression;
			}

			return $expression;
		});

		if (!$this->getConfig('autoFinder')) {
			return;
		}

		foreach ($this->_table->associations() as $association) {
			/** @var \Cake\ORM\Association|\Cake\ORM\Table $association */
			if ($association->getTarget()->hasBehavior('Expose') && $association->getFinder() === 'all') {
				if (!$association->hasBehavior('Superimpose')) {
					$association->addBehavior('Expose.Superimpose');
				}
				$association->setFinder('superimpose');
			}
		}

		$query = $query->find('superimpose');
	}

	/**
	 * Custom finder that superimposes the primary key with the UUID in returned result set.
	 *
	 * @param \Cake\ORM\Query $query Query.
	 * @param array $options Options.
	 * @return \Cake\ORM\Query
	 */
	public function findSuperimpose(Query $query, array $options) {
		$query->formatResults(function (ResultSetInterface $results) {
			return $results->map(function ($row) {
				$pk = $this->_table->getPrimaryKey();
				$field = $this->_table->getExposedKey();
				if (!isset($row[$field])) {
					return $row;
				}

				$alias = $this->getConfig('primaryKeyField');
				$row[$alias] = $row[$pk] ?? null;
				$row[$pk] = $row[$field];

				return $row;
			});
		});

		return $query;
	}

	/**
	 * @param string $uuid UUID to resolve
	 *
	 * @return string|int
	 */
	protected function resolve(string $uuid) {
		$primaryKey = $this->_table->getPrimaryKey();

		$record = $this->_table
			->find()
			->where([$this->_table->getExposedKey() => $uuid])
			->select([$primaryKey])
			->firstOrFail();

		return $record->$primaryKey;
	}

}
