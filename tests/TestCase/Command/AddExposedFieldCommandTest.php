<?php

namespace Expose\Test\TestCase\Command;

use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Expose\Command\AddExposedFieldCommand;
use ReflectionClass;

class AddExposedFieldCommandTest extends TestCase {

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
	 * @var \Cake\Console\ConsoleIo|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected $io;

	/**
	 * @var \Expose\Command\AddExposedFieldCommand
	 */
	protected $command;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Users = TableRegistry::getTableLocator()->get('Users');

		$this->io = $this->getMockBuilder(ConsoleIo::class)->getMock();
		$this->command = new AddExposedFieldCommand($this->io);

		$this->setAppNamespace();
		$this->useCommandRunner();
	}

	/**
	 * @return void
	 */
	public function testMigrationExists(): void {
		$name = 'MigrationFoo';
		$path = TESTS . 'test_app' . DS . 'config' . DS . 'Migrations' . DS;
		$result = $this->invokeMethod($this->command, 'migrationExists', [$name, $path]);
		$this->assertFalse($result);

		$name = 'MigrationFooBar';
		$result = $this->invokeMethod($this->command, 'migrationExists', [$name, $path]);
		$this->assertTrue($result);

		$name = 'MigrationBaz';
		$result = $this->invokeMethod($this->command, 'migrationExists', [$name, $path]);
		$this->assertTrue($result);
	}

	/**
	 * @return void
	 */
	public function testExecute(): void {
		$this->io->expects($this->any())->method('askChoice')->willReturn('yes');

		$this->exec('add_exposed_field Users');

		$this->assertExitCode(Command::CODE_SUCCESS);
	}

	/**
	 * @return void
	 */
	public function testExecuteNewField(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['field' => 'binary_uuid']);

		$this->exec('add_exposed_field Users -d', ['yes']);

		$this->assertExitCode(Command::CODE_SUCCESS);

		$this->assertOutputContains('Migration to be created: MigrationExposedFieldUsers');

		$this->assertOutputContains('$table->addColumn(\'binary_uuid\', \'uuid\', [');
		$this->assertOutputContains('\'null\' => true,');

		$this->assertOutputContains('$table->addIndex([\'binary_uuid\'], [\'unique\' => true]);');
	}

	/**
	 * @param object &$object Instantiated object that we will run method on.
	 * @param string $methodName Method name to call.
	 * @param array $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	protected function invokeMethod(&$object, string $methodName, array $parameters = []) {
		$reflection = new ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

}
