<?php

namespace Expose\Test\TestCase\Command;

use Cake\Command\Command;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class PopulateExposedFieldCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	/**
	 * @var string[]
	 */
	protected $fixtures = [
		'plugin.Expose.ExistingRecords',
	];

	/**
	 * @var \TestApp\Model\Table\ExistingRecordsTable
	 */
	protected $ExistingRecords;

	/**
	 * @var \Expose\Command\PopulateExposedFieldCommand
	 */
	protected $command;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->ExistingRecords = TableRegistry::getTableLocator()->get('ExistingRecords');
		$this->ExistingRecords->deleteAll('1=1');
		$this->ExistingRecords->removeBehavior('Expose');
		$data = [
			[
				'name' => 'One',
			],
			[
				'name' => 'Two',
			],
		];
		$entities = $this->ExistingRecords->newEntities($data);
		$this->ExistingRecords->saveManyOrFail($entities);

		$this->ExistingRecords->addBehavior('Expose.Expose');

		$this->setAppNamespace();
		$this->useCommandRunner();
	}

	/**
	 * @return void
	 */
	public function testExecute(): void {
		$this->exec('populate_exposed_field ExistingRecords');

		$this->assertExitCode(Command::CODE_SUCCESS);
		$this->assertOutputContains('Populated 2 records. Nothing else left.');
	}

}
