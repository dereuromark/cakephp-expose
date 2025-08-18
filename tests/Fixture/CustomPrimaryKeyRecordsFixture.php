<?php
declare(strict_types = 1);

namespace Expose\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CustomPrimaryKeyRecordsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public array $fields = [
		'code' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
		'uuid' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
		'modified' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['code'], 'length' => []],
			'custom-records-uuid' => ['type' => 'unique', 'columns' => ['uuid'], 'length' => []],
		],
		'_options' => [
			'engine' => 'InnoDB',
			'collation' => 'utf8_unicode_ci',
		],
	];

	/**
	 * Init method
	 *
	 * @return void
	 */
	public function init(): void {
		$this->records = [
			[
				'code' => 'RECORD-001',
				'uuid' => null,
				'name' => 'First Record',
				'created' => '2020-02-24 08:21:27',
				'modified' => '2020-02-24 08:21:27',
			],
			[
				'code' => 'RECORD-002',
				'uuid' => null,
				'name' => 'Second Record',
				'created' => '2020-02-24 08:21:27',
				'modified' => '2020-02-24 08:21:27',
			],
		];
		parent::init();
	}

}
