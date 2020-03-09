<?php

namespace Expose\Test\TestCase\Command;

use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Expose\Command\PopulateExposedFieldCommand;

class PopulateExposedFieldCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	/**
	 * @var string[]
	 */
	protected $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.CustomFieldRecords',
		'plugin.Expose.ExistingRecords',
		'plugin.Expose.BinaryFieldRecords',
	];

	/**
	 * @var \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior
	 */
	protected $Users;

	/**
	 * @var \Expose\Command\PopulateExposedFieldCommand
	 */
	protected $command;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Users = TableRegistry::getTableLocator()->get('Users');

		$this->io = $this->getMockBuilder(ConsoleIo::class)->getMock();
		$this->command = new PopulateExposedFieldCommand($this->io);

		$this->setAppNamespace();
		$this->useCommandRunner();
	}

	/**
	 * @return void
	 */
	public function testExecute(): void {
		$this->exec('populate_exposed_field Users');

		$this->assertExitCode(Command::CODE_SUCCESS);
		$this->assertOutputContains('Populated 0 records. Nothing else left.');
	}

}
