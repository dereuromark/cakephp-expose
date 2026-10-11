<?php

namespace Expose\Test\TestCase\Command;

use Cake\Command\Command;
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
		$this->exec('expose add_field Users');

		$this->assertExitCode(Command::CODE_SUCCESS);
	}

	/**
	 * @return void
	 */
	public function testExecuteNewField(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['field' => 'binary_uuid']);

		$this->exec('expose add_field Users -d', ['yes']);

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

		$this->exec('expose add_field Users -b -d', ['yes']);

		$this->assertExitCode(Command::CODE_SUCCESS);

		$this->assertOutputContains('Migration to be created: MigrationExposedFieldUsers');

		$this->assertOutputContains('$table->addColumn(\'binary_uuid\', \'binary\', [');
		$this->assertOutputContains('\'limit\' => 16,');
		$this->assertOutputContains('\'null\' => true,');

		$this->assertOutputContains('$table->addIndex([\'binary_uuid\'], [\'unique\' => true]);');
	}

	public function testExistingBinaryFieldKeepsItsStorageType(): void {
		$column = $this->Users->getSchema()->getColumn('uuid');
		$column['type'] = 'binaryuuid';
		$column['null'] = true;
		$this->Users->getSchema()->addColumn('uuid', $column);

		$this->exec('expose add_field Users');

		$this->assertExitCode(Command::CODE_SUCCESS);
		$this->assertOutputContains("changeColumn('uuid', 'binary', [");
		$this->assertOutputContains("'limit' => 16,");
	}

	public function testExistingNativeUuidKeepsItsStorageType(): void {
		$column = $this->Users->getSchema()->getColumn('uuid');
		$column['type'] = 'nativeuuid';
		$column['null'] = true;
		$this->Users->getSchema()->addColumn('uuid', $column);

		$this->exec('expose add_field Users');

		$this->assertExitCode(Command::CODE_SUCCESS);
		$this->assertOutputContains("changeColumn('uuid', 'nativeuuid', [");
	}

	public function testVendorPluginGetsAValidDefaultMigrationName(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['field' => 'public_uuid']);
		TableRegistry::getTableLocator()->set('My/PluginName.Users', $this->Users);

		$this->exec('expose add_field My/PluginName.Users -d', ['yes']);

		$this->assertExitCode(Command::CODE_SUCCESS);
		$this->assertOutputContains('class MigrationExposedFieldMyPluginNameUsers extends BaseMigration');
	}

	public function testInvalidMigrationNameIsRejected(): void {
		$this->Users->removeBehavior('Expose');
		$this->Users->addBehavior('Expose.Expose', ['field' => 'public_uuid']);

		$this->exec('expose add_field Users ../Invalid -d', ['yes']);

		$this->assertExitCode(Command::CODE_ERROR);
		$this->assertErrorContains('Migration name must be a valid PHP class name.');
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
