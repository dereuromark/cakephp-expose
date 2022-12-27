<?php

$tables = [];

/** @var \DirectoryIterator<\DirectoryIterator> $ierator */
$ierator = new DirectoryIterator(__DIR__ . DS . 'Fixture');
foreach ($ierator as $file) {
	if (!preg_match('/(\w+)Fixture.php$/', (string)$file, $matches)) {
		continue;
	}

	$name = $matches[1];
	$tableName = \Cake\Utility\Inflector::underscore($name);
	$class = 'Expose\\Test\\Fixture\\' . $name . 'Fixture';
	try {
		$object = (new \ReflectionClass($class))->getProperty('fields');
	} catch (ReflectionException $e) {
		continue;
	}

	$array = $object->getDefaultValue();
	$constraints = $array['_constraints'] ?? [];
	$indexes = $array['_indexes'] ?? [];
	unset($array['_constraints'], $array['_indexes'], $array['_options']);
	$table = [
		'table' => $tableName,
		'columns' => $array,
		'constraints' => $constraints,
		'indexes' => $indexes,
	];
	$tables[$tableName] = $table;
}

return $tables;

/*
return [
	[
		'table' => 'users',
		'columns' => [
			'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
			'uuid' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
			'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null],
			'created' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
			'modified' => ['type' => 'datetime', 'length' => null, 'precision' => null, 'null' => false, 'default' => null, 'comment' => ''],
		],
		'constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
			'uuid' => ['type' => 'unique', 'columns' => ['uuid'], 'length' => []],
		],
	],
];
*/
