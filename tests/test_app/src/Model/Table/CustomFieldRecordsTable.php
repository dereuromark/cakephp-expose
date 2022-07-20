<?php
declare(strict_types = 1);

namespace TestApp\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ExposedUsers Model
 *
 * @method \TestApp\Model\Entity\CustomFieldRecord newEmptyEntity()
 * @method \TestApp\Model\Entity\CustomFieldRecord newEntity(array $data, array $options = [])
 * @method array<\TestApp\Model\Entity\CustomFieldRecord> newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\CustomFieldRecord get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\CustomFieldRecord findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\CustomFieldRecord patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TestApp\Model\Entity\CustomFieldRecord> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\CustomFieldRecord|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\CustomFieldRecord saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\TestApp\Model\Entity\CustomFieldRecord>|false saveMany(iterable $entities, $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\TestApp\Model\Entity\CustomFieldRecord> saveManyOrFail(iterable $entities, $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\TestApp\Model\Entity\CustomFieldRecord>|false deleteMany(iterable $entities, $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\TestApp\Model\Entity\CustomFieldRecord> deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Expose\Model\Behavior\ExposeBehavior
 */
class CustomFieldRecordsTable extends Table {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->addBehavior('Timestamp');
		$this->addBehavior('Expose.Expose', ['field' => 'public_identifier']);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->integer('id')
			->allowEmptyString('id', null, 'create');

		$validator
			->uuid('public_identifier')
			->notEmptyString('public_identifier');

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): RulesChecker {
		// We do this using DB constraint.
		//$rules->add($rules->isUnique(['public_identifier']));

		return $rules;
	}

}
