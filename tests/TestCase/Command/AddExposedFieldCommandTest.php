<?php

namespace Expose\Test\TestCase\Command;

use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Expose\Command\AddExposedFieldCommand;
use ReflectionClass;
use TestApp\Model\Table\UsersTable;

class AddExposedFieldCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	/**
	 * @var array<string>
	 */
	protected array $fixtures = [
		'plugin.Expose.Users',
		'plugin.Expose.CustomFieldRecords',
		'plugin.Expose.ExistingRecords',
		'plugin.Expose.BinaryFieldRecords',
	];

	/**
	 * @var \TestApp\Model\Table\UsersTable
	 */
	protected UsersTable $Users;

	/**
	 * @var \Cake\Console\ConsoleIo|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected $io;

	/**
	 * @var \Expose\Command\AddExposedFieldCommand
	 */
	protected AddExposedFieldCommand $command;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['Expose']);

		$this->Users = TableRegistry::getTableLocator()->get('Users');

		$this->io = $this->getMockBuilder(ConsoleIo::class)->getMock();
		$this->command = new AddExposedFieldCommand();

		$this->setAppNamespace();
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
	 * @return void
	 */
	public function testExecuteNewFieldBinary(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['field' => 'binary_uuid']);

		$this->exec('add_exposed_field Users -b -d', ['yes']);

		$this->assertExitCode(Command::CODE_SUCCESS);

		$this->assertOutputContains('Migration to be created: MigrationExposedFieldUsers');

		$this->assertOutputContains('$table->addColumn(\'binary_uuid\', \'binary\', [');
		$this->assertOutputContains('\'limit\' => 16,');
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
	protected function invokeMethod(object &$object, string $methodName, array $parameters = []): mixed {
		$reflection = new ReflectionClass($object::class);
		$method = $reflection->getMethod($methodName);

		return $method->invokeArgs($object, $parameters);
	}

}
