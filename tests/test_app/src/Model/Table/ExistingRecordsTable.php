<?php
declare(strict_types = 1);

namespace TestApp\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ExposedUsers Model
 *
 * @method \TestApp\Model\Entity\User newEmptyEntity()
 * @method \TestApp\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \TestApp\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \TestApp\Model\Entity\User get($primaryKey, $options = [])
 * @method \TestApp\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \TestApp\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TestApp\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TestApp\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \TestApp\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Expose\Model\Behavior\ExposeBehavior
 */
class ExistingRecordsTable extends Table {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->addBehavior('Timestamp');
		$this->addBehavior('Expose.Expose');
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
			->uuid('uuid')
			->notEmptyString('uuid');

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
		//$rules->add($rules->isUnique(['uuid']));

		return $rules;
	}

}
