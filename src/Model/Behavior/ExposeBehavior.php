<?php

namespace Expose\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Text;
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
	 * - generator: Set to a custom callable/closure if you don't want default UUID 4 (random UUIDs).
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'field' => 'uuid',
		'on' => 'beforeMarshal',
		'implementedFinders' => [
			'exposed' => 'findExposed',
			'exposedList' => 'findExposedList',
		],
		'implementedMethods' => [
			'getExposedKey' => 'getExposedKey',
			'initExposedField' => 'initExposedField',
		],
		'generator' => null,
	];

	/**
	 * Table instance
	 *
	 * @var \Cake\ORM\Table
	 */
	protected $_table;

	/**
	 * @param \Cake\ORM\Table $table
	 * @param array $config
	 */
	public function __construct(Table $table, array $config = []) {
		$config += (array)Configure::read('Expose');

		parent::__construct($table, $config);
	}

	/**
	 * @param \Cake\ORM\Query $query
	 * @param array $options
	 *
	 * @return \Cake\Datasource\QueryInterface|\Cake\ORM\Query
	 */
	public function findExposedList(Query $query, array $options) {
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
			$field = $this->getTable()->getAlias() . '.' . $field;
		}

		return $field;
	}

	/**
	 * Custom finder exposed as
	 *
	 * ->find('exposed', ['uuid' => $uuid])
	 *
	 * @param \Cake\ORM\Query $query
	 * @param array $options
	 * @return \Cake\ORM\Query
	 * @throws \InvalidArgumentException If the 'slug' key is missing in options
	 */
	public function findExposed(Query $query, array $options) {
		$field = $this->getConfig('field');
		if (empty($options[$field])) {
			throw new InvalidArgumentException('The `' . $field . '` key is required for find(\'exposed\')');
		}

		return $query->where([$this->getTable()->getAlias() . '.' . $field => $options[$field]]);
	}

	/**
	 * Using marshalling is needed if you want to use the exposed ID before persisting the entity.
	 * Otherwise use beforeSave callback.
	 *
	 * This callback will auto-add the exposed ID field into the fields whitelist as well as accessibleFields.
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \ArrayObject $data
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options) {
		if ($this->_config['on'] !== 'beforeMarshal') {
			return;
		}

		$field = $this->getConfig('field');
		$data[$field] = $this->generateExposedField();

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
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		if ($this->_config['on'] !== 'beforeSave') {
			return;
		}

		$field = $this->getConfig('field');
		$entity->$field = $this->generateExposedField();
	}

	/**
	 * Set missing UUIDS for an existing entity.
	 *
	 * The field has been added as "DEFAULT NULL" here to allow the null fields to be populated.
	 * After this, the field can be migrated to "DEFAULT NOT NULL" constraint and a unique index.
	 *
	 * @param array $params
	 * @return int
	 * @throws \RuntimeException
	 */
	public function initExposedField($params = []) {
		$field = $this->getConfig('field');
		if (!$this->_table->hasField($field)) {
			throw new RuntimeException('Table does not have exposed field `' . $field . '`');
		}

		$defaults = [
			'limit' => 1000,
			'conditions' => [$field . ' IS' => null],
		];
		$params = array_merge($defaults, $params);

		$count = 0;

		while (($records = $this->_table->find('all', $params)->toArray())) {
			/** @var \Cake\ORM\Entity $record */
			foreach ($records as $record) {
				$record->$field = $this->generateExposedField();

				$this->_table->saveOrFail($record);
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Uses UUID version 4 by default.
	 *
	 * @return string
	 */
	public function generateExposedField(): string {
		$generator = $this->getConfig('generator');
		if ($generator !== null) {
			return $generator();
		}

		return Text::uuid();
	}

}
