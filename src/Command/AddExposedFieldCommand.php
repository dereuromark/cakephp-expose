<?php

namespace Expose\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use DirectoryIterator;

/**
 * Structure migration assistance for exposed fields.
 */
class AddExposedFieldCommand extends Command {

	/**
	 * @var string
	 */
	protected $migrationPath = CONFIG . 'Migrations' . DS;

	/**
	 * E.g.:
	 * bin/cake add_exposed_field PluginName.ModelName {MigrationName}
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 *
	 * @return int|null The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$model = $args->getArgument('model');

		$this->loadModel($model);
		[$prefix, $name] = pluginSplit($model);

		/** @var \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $table */
		$table = $this->$name;
		if (get_class($table) === Table::class) {
			$io->abort('This table class cannot be found (' . get_class($table) . ' was used). Please check the name of your table class.');
		}
		if (!$table->hasBehavior('Expose')) {
			$io->abort('You need to attach the Expose.Expose behavior to this model first (' . get_class($table) . '). Then we can create the migration for it.');
		}

		$field = $table->getExposedKey();
		$fieldExists = $table->hasField($field);

		if ($fieldExists) {
			$schema = $table->getSchema()->getColumn($field);
			if ($schema['null'] !== false) {
				$io->success('Nothing to be done. The field exists and is already set to NOT NULL.');

				return static::CODE_SUCCESS;
			}

			$io->out('Field has been detected as existing. You want to now make this field not nullable once all records have been populated.');
			$count = $table->find()->where([$field . ' IS' => null])->count();
			if ($count) {
				$io->abort($count . ' records require the exposed field to be populated with UUID data. Use the `populate_exposed_field` command for this.');
			}

			$io->out('Use the following snippet in a follow up migration now:');

			$migrationContent = $this->generateUpdateOperation($table);
			$io->out('');
			$io->out($migrationContent);
			$io->out('');

			return static::CODE_SUCCESS;
		}

		$containsRecords = $io->askChoice('Does this table already contain records? If so, we need to create a nullable field.', ['no', 'yes'], 'yes') === 'yes';

		$migrationName = $args->getArgument('migration') ?: 'MigrationExposedField' . $prefix . $name;
		$migrationFilePath = $this->migrationPath . date('YmdHis') . '_' . $migrationName . '.php';

		$io->out('Migration to be created: ' . $migrationName);
		$io->out('File to be created: ' . $migrationFilePath);
		$io->out('You will still have to execute the migration afterwards manually (`bin/cake migrations migrate`).', 1, $io::VERBOSE);

		$continue = $args->getOption('dry-run') || $io->askChoice('Continue?', ['no', 'yes'], 'yes') === 'yes';
		if (!$continue) {
			$io->abort('Aborted.');
		}

		$io->out('Creating ' . $model . ' `' . $field . '` field migration ...');

		$migrationContent = $this->generateMigration($migrationName, $table, $containsRecords);
		if (!$args->getOption('dry-run')) {
			if ($this->migrationExists($migrationName, $this->migrationPath)) {
				$io->abort('File already exists: ' . $migrationFilePath);
			}
			file_put_contents($migrationFilePath, $migrationContent);
			$io->success('Migration file created. Now you can migrate your table(s) using `bin/cake migrations migrate`.');
		} else {
			$io->out('--- ' . $migrationName . '.php' . ' ---');
			$io->out('');
			$io->out($migrationContent);
			$io->out('');
		}

		return static::CODE_SUCCESS;
	}

	/**
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 *
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = parent::buildOptionParser($parser);
		$parser->setDescription('Initialize the exposed field for all existing records. This requires the Expose.Expose behavior to be attached to this table class as well as the migration for the field to be added being executed.');

		$parser->addArgument('model', [
			'required' => true,
		]);
		$parser->addArgument('migration', [
			'required' => false,
		]);
		$parser->addOption('dry-run', [
			'short' => 'd',
			'help' => 'Dry-Run it (just output the file content instead of creating it)',
			'boolean' => true,
		]);

		return $parser;
	}

	/**
	 * @param string $migrationName
	 * @param \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $table
	 * @param bool $containsRecords
	 *
	 * @return string
	 */
	protected function generateMigration(string $migrationName, Table $table, bool $containsRecords): string {
		$operations = $this->generateOperations($table, $containsRecords);

		$migration = <<<TXT
<?php
use Migrations\AbstractMigration;

class $migrationName extends AbstractMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
$operations
    }
}
TXT;
		return $migration;
	}

	/**
	 * @param \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $table
	 * @param bool $containsRecords
	 *
	 * @return string
	 */
	protected function generateOperations(Table $table, bool $containsRecords): string {
		$field = $table->getExposedKey();
		$tableName = $table->getTable();
		$null = $containsRecords ? 'true' : 'false';

		$operations = <<<TXT
        \$table = \$this->table('$tableName');
        \$table->addColumn('$field', 'uuid', [
            'default' => null,
            'null' => $null, // Add it as true for existing entities first, then fill/populate, then set to false afterwards.
        ]);
        \$table->addIndex(['$field'], ['unique' => true]);
        \$table->update();
TXT;

		return $operations;
	}

	/**
	 * @param \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $table
	 *
	 * @return string
	 */
	protected function generateUpdateOperation(Table $table): string {
		$field = $table->getExposedKey();
		$tableName = $table->getTable();

		$operations = <<<TXT
        \$table = \$this->table('$tableName');
        \$table->changeColumn('$field', 'uuid', [
            'default' => null,
            'null' => false, // Now that all records are populated, we can force this to be not null
        ]);
        \$table->update();
TXT;

		return $operations;
	}

	/**
	 * @param string $migrationName
	 * @param string $migrationPath
	 *
	 * @return bool
	 */
	protected function migrationExists(string $migrationName, string $migrationPath): bool {
		$inflectedMigrationName = Inflector::underscore($migrationName);

		/** @var \DirectoryIterator[]\SplFileInfo[] $iterator */
		$iterator = new DirectoryIterator($migrationPath);
		foreach ($iterator as $fileInfo) {
			if ($fileInfo->isDot()) {
				continue;
			}

			if (preg_match('#_(' . $migrationName . '|' . $inflectedMigrationName . ')\.php$#', $fileInfo->getFilename())) {
				return true;
			};
		}

		return false;
	}

}
