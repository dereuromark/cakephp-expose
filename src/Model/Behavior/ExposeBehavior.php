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
use Closure;
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
	protected array $defaultConfig = [
		'field' => 'uuid',
		'on' => 'beforeSave',
		'implementedFinders' => [
			'exposed' => 'findExposed',
			'exposedList' => 'findExposedList',
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
	 * @param \Cake\ORM\Query\SelectQuery $query Query
	 * @param \Closure|array|string|null $keyField List key
	 * @param \Closure|array|string|null $valueField List value
	 * @param \Closure|array|string|null $groupField List group
	 * @param string $valueSeparator Separator for multiple value fields
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findExposedList(
		SelectQuery $query,
		Closure|array|string|null $keyField = null,
		Closure|array|string|null $valueField = null,
		Closure|array|string|null $groupField = null,
		string $valueSeparator = ' ',
	): SelectQuery {
		return $query->find(
			'list',
			keyField: $keyField ?? $this->getConfig('field'),
			valueField: $valueField ?? $this->table()->getDisplayField(),
			groupField: $groupField,
			valueSeparator: $valueSeparator,
		);
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
	 * ->find('exposed', uuid: $uuid)
	 *
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param string $uuid
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findExposed(SelectQuery $query, string $uuid): SelectQuery {
		$field = $this->getConfig('field');

		return $query->where([$this->table()->getAlias() . '.' . $field => $uuid]);
	}

	/**
	 * Using marshalling is needed if you want to use the exposed ID before persisting the entity.
	 * Otherwise, use beforeSave callback.
	 *
	 * This is only allowed/possible for (mass)creating or updating records.
	 * It will overwrite all fields! Careful with using this callback as it does not separate between create and update.
	 *
	 * This callback will auto-add the exposed ID field into the fields whitelist as well as patchableFields.
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \ArrayObject $data
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void {
		if ($this->config['on'] !== 'beforeMarshal') {
			return;
		}

		$field = $this->getConfig('field');
		$data[$field] = $this->generateExposedField($field);

		if (isset($options['fields']) && !in_array($field, $options['fields'], true)) {
			$options['fields'][] = $field;
		}

		if (!isset($options['patchableFields'])) {
			$options['patchableFields'] = [];
		}
		$options['patchableFields'][$field] = true;
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
		if ($this->config['on'] !== 'beforeSave') {
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
		if (!$this->table()->hasField($field)) {
			throw new RuntimeException('Table does not have exposed field `' . $field . '`');
		}

		/** @var string $primaryKey */
		$primaryKey = $this->table()->getPrimaryKey();
		$defaults = [
			'limit' => 1000,
			'conditions' => [$field . ' IS' => null],
			'fields' => (array)$primaryKey,
		];
		$params = array_merge($defaults, $params);

		$count = 0;

		while (($records = $this->table()->find()->applyOptions($params)->where([$this->table()->aliasField($field) . ' IS' => null])->toArray())) {
			/** @var \Cake\ORM\Entity $record */
			foreach ($records as $record) {
				$uuid = $this->generateExposedField($field);

				$this->table()->updateAll([$field => $uuid], [$primaryKey => $record->get($primaryKey)]);
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
		$fieldType = $this->table()->getSchema()->getColumnType($field);
		if (!$fieldType) {
			$schema = $this->table()->getSchema()->getColumn($field);

			throw new RuntimeException('Cannot determine column type of field `' . $field . '`. Found type: ' . ($schema['type'] ?? 'unknown'));
		}

		$type = TypeFactory::build($fieldType);

		return $type->newId();
	}

}
