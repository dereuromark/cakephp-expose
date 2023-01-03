<?php

namespace Expose\Test\TestCase\Command;

use Cake\Command\Command;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Expose\Command\PopulateExposedFieldCommand;
use TestApp\Model\Table\ExistingRecordsTable;

class PopulateExposedFieldCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Expose.ExistingRecords',
	];

	/**
	 * @var \TestApp\Model\Table\ExistingRecordsTable
	 */
	protected ExistingRecordsTable $ExistingRecords;

	/**
	 * @var \Expose\Command\PopulateExposedFieldCommand
	 */
	protected PopulateExposedFieldCommand $command;

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
		//$this->useCommandRunner();
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
