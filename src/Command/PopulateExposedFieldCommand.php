<?php

namespace Expose\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Data migration assistance for exposed fields.
 */
class PopulateExposedFieldCommand extends Command {

	/**
	 * E.g.:
	 * bin/cake populate_exposed_field PluginName.ModelName
	 *
	 * @param \Cake\Console\Arguments $args The command arguments.
	 * @param \Cake\Console\ConsoleIo $io The console io
	 *
	 * @return int|null The exit code or null for success
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$table = $args->getArgument('table');

		$this->loadModel($table);
		[$prefix, $name] = pluginSplit($table);

		/** @var \Cake\ORM\Table|\Expose\Model\Behavior\ExposeBehavior $model */
		$model = $this->$name;

		$field = $model->getExposedKey();
		$io->out('Populating ' . $table . ' `' . $field . '` field ...');

		$count = $model->initExposedField();

		$io->success('Populated ' . $count . ' records. Nothing else left.');

		return static::CODE_SUCCESS;
	}

	/**
	 * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
	 *
	 * @return \Cake\Console\ConsoleOptionParser The built parser.
	 */
	protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser {
		$parser = parent::buildOptionParser($parser);
		$parser->setDescription('Populate the exposed field for all existing records. This requires the Expose.Expose behavior to be attached to this table class as well as the migration for the field to be added being executed.');

		$parser->addArgument('table', [
			'required' => true,
		]);

		return $parser;
	}

}
