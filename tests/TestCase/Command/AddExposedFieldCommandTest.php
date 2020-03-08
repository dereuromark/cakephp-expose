<?php

namespace Expose\Test\TestCase\Command;

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
		$this->skipIf(true);

		$this->exec('add_exposed_field Users');
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
