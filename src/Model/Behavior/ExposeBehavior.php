<?php

namespace Expose\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Database\Query\SelectQuery;
use Cake\Database\TypeFactory;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use InvalidArgumentException;
use RuntimeException;

/**
 * Replaces HashId plugin and approach.
 *
 * Usage: See docs
 *
 * @author Mark Scherer
 * @license MIT
 */
class ExposeBehavior extends Behavior {

	/**
	 * Default config
	 *
	 * - field: The exposed field name
	 *
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'field' => 'uuid',
		'on' => 'beforeSave',
		'implementedFinders' => [
			'exposed' => 'findExposed',
			'exposedList' => 'findExposedList',
		],
		'implementedMethods' => [
			'getExposedKey' => 'getExposedKey',
			'initExposedField' => 'initExposedField',
		],
	];

	/**
	 * @param \Cake\ORM\Table $table
	 * @param array $config
	 */
	public function __construct(Table $table, array $config = []) {
		$config += (array)Configure::read('Expose');

		parent::__construct($table, $config);
	}

	/**
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param array $options
	 *
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findExposedList(SelectQuery $query, array $options): SelectQuery {
		$options += [
			'keyField' => $this->getConfig('field'),
			'valueField' => $this->_table->getDisplayField(),
			'groupField' => null,
		];

		return $query->find('list', $options);
	}

	/**
	 * @param bool $prefixed
	 *
	 * @return string
	 */
	public function getExposedKey(bool $prefixed = false): string {
		$field = $this->getConfig('field');
		if ($prefixed) {
			$field = $this->table()->getAlias() . '.' . $field;
		}

		return $field;
	}

	/**
	 * Custom finder exposed as
	 *
	 * ->find('exposed', ['uuid' => $uuid])
	 *
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param array $options
	 * @throws \InvalidArgumentException If the 'slug' key is missing in options
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findExposed(SelectQuery $query, array $options): SelectQuery {
		$field = $this->getConfig('field');
		if (empty($options[$field])) {
			throw new InvalidArgumentException('The `' . $field . '` key is required for find(\'exposed\')');
		}

		return $query->where([$this->table()->getAlias() . '.' . $field => $options[$field]]);
	}

	/**
	 * Using marshalling is needed if you want to use the exposed ID before persisting the entity.
	 * Otherwise, use beforeSave callback.
	 *
	 * This is only allowed/possible for (mass)creating or updating records.
	 * It will overwrite all fields! Careful with using this callback as it does not separate between create and update.
	 *
	 * This callback will auto-add the exposed ID field into the fields whitelist as well as accessibleFields.
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \ArrayObject $data
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void {
		if ($this->_config['on'] !== 'beforeMarshal') {
			return;
		}

		$field = $this->getConfig('field');
		$data[$field] = $this->generateExposedField($field);

		if (isset($options['fields'])) {
			if (!in_array($field, $options['fields'], true)) {
				$options['fields'][] = $field;
			}
		}

		if (!isset($options['accessibleFields'])) {
			$options['accessibleFields'] = [];
		}
		$options['accessibleFields'][$field] = true;
	}

	/**
	 * Using beforeSave is recommended if you only want to access the exposed ID after persisting the entity.
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		if ($this->_config['on'] !== 'beforeSave') {
			return;
		}

		if (!$entity->isNew()) {
			return;
		}

		$field = $this->getConfig('field');
		$entity->$field = $this->generateExposedField($field);
	}

	/**
	 * Set missing UUIDS for an existing entity.
	 *
	 * The field has been added as "DEFAULT NULL" here to allow the null fields to be populated.
	 * After this, the field can be migrated to "DEFAULT NOT NULL" constraint and a unique index.
	 *
	 * @param array $params
	 * @throws \RuntimeException
	 * @return int
	 */
	public function initExposedField(array $params = []): int {
		$field = $this->getConfig('field');
		if (!$this->_table->hasField($field)) {
			throw new RuntimeException('Table does not have exposed field `' . $field . '`');
		}

		$defaults = [
			'limit' => 1000,
			'conditions' => [$field . ' IS' => null],
			'fields' => (array)$this->_table->getPrimaryKey(),
		];
		$params = array_merge($defaults, $params);

		$count = 0;

		while (($records = $this->_table->find('all', $params)->toArray())) {
			/** @var \Cake\ORM\Entity $record */
			foreach ($records as $record) {
				$uuid = $this->generateExposedField($field);

				$this->_table->updateAll(['uuid' => $uuid], ['id' => $record->id]);
				$count++;
			}
		}

		return $count;
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function generateExposedField(string $field): string {
		$fieldType = $this->_table->getSchema()->getColumnType($field);
		if (!$fieldType) {
			throw new RuntimeException('Cannot determine column type of field `' . $field . '`');
		}

		$type = TypeFactory::build($fieldType);

		return $type->newId();
	}

}
