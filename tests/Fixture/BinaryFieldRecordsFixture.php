<?php
declare(strict_types = 1);

namespace Expose\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BinaryFieldRecordsFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public array $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'uuid' => ['type' => 'binaryuuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
		'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
		'modified' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
			'uuid' => ['type' => 'unique', 'columns' => ['uuid'], 'length' => []],
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
				'uuid' => 'eb25610d-7bfa-4e34-812c-ad72b100fb26',
				'name' => 'Foo Bar',
				'created' => '2020-02-24 08:21:27',
				'modified' => '2020-02-24 08:21:27',
			],
		];
		parent::init();
	}

}
