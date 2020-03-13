### Expose Plugin Documentation

### Set Up

Make sure you set  up your entity with a UUID field for exposure.
By default it is expected to be named `uuid`.
You can configure it to be any other key.

The primary key `id` (auto increment integer) will not be touched by this plugin/behavior.

#### Adding the Behavior
In your Table class, add this in your `initialize()` method:
```php
public function initialize(array $config): void {
    parent::initialize($config);

    ...
    $this->addBehavior('Expose.Expose');
}
```

#### New Table Migration
```php
// Add your exposed field
$table->addColumn('uuid', 'uuid', [
    'default' => null,
    'null' => false, // Add it as true for existing entities first, then fill/populate, then set to false afterwards.
]);

// Besides primary key we will also want to have a unique index for quicker lookup on this exposed lookup field.
$table->addIndex(['uuid'], ['unique' => true]);

$table->create();
```
If you want to save disk space, you can also use binary UUID (16 instead of 32 char length) with `'type' => 'binaryuuid'`.

#### Existing Table Migration
```php
// Add it as 'null' => true for existing entities first, then fill/populate, then set to false afterwards.
$table->addColumn('uuid', 'uuid', [
    'default' => null,
    'null' => false,
]);

$table->addIndex(['uuid'], ['unique' => true]);

$table->update();
```

Use the command here to generate a migration file for you:
```
bin/cake add_exposed_field PluginName.ModelName {MigrationName}
```
With `-d`/`--dry-run` you can output first what would be generated.

Then execute the migration using `bin/cake migrations migrate`.


#### Entity update
You want to make sure that neither primary key, nor this exposed field is patchable (when marshalling = mass assignment):
```php
protected $_accessible = [
    '*' => true,
    'id' => false,
    'uuid' => false, // Your exposed field
];
```

Now you are all set to go.

### Usage

When saving an entity it will auto-add the exposed field when marshalling/patching.
If you don't need this before the entity is persisted, it is recommended to  do this upon save:

```php
public function initialize(array $config): void {
    parent::initialize($config);

    ...
    $this->addBehavior('Expose.Expose', ['on' => 'beforeSave']);
}
```

Then you would query now in your public actions based on this exposed field:
```php
/**
 * @param string|null $uuid Exposed UUID.
 *
 *@return \Cake\Http\Response|null|void
 */
public function view($uuid = null) {
    $field = $this->Users->getExposedKey();
    $user = $this->Users->find('exposed', [$field => $uuid])->firstOrFail();

    $this->set(compact('user'));
}
```
Instead of primary key `id` and ->get($id) we work on `uuid` field now for public access.

And you can link in your templates using this exposed field instead:
```php
<?php echo $this->Html->link($user->name, ['action' => 'view', $user->uuid]); ?>
```

#### Replacement for find('list')

There is also a list replacement:
```php
/** @var string[] $users
$exposedUsers = $this->ExposedUsers
    ->find('exposedList')
    ->toArray();
```

#### Using the key directly in queries
In some cases you might want to manually use the field in a query. Here it is recommended to get the field as prefixed version:
```php
$field = $this->Users->getExposedKey(true); // ModelName.field_name
...
->where([..., $field => $value)
...
```
Especially when you `contain` other relations, you should always prefix the fields to avoid naming collisions.

#### Pagination restrictions
Set a sortWhitelist into your pagination config:
```php
    'sortWhitelist' => [
        'name',
    ],
```
The `id` should not be sortable or filterable here.

#### Using a different UUID generator

It uses UUID version 4 generator from CakePHP Text utility library by default.
If you want to use a different generator, you can set a closure:
```php
'generator' => function () {
    return MyAwesomeUuid::generate(); // or alike
}
```

### Populating existing records

The behavior ships with a convenience command to be called from CLI.
So just run this to populate the existing records with the missing UUID data.
```
bin/cake populate_exposed_field PluginName.ModelName
```

Make sure the `Expose.Expose` behavior is attached to this table class.
Also execute the migration for the field to be added prior to running this.

Once all records are populated, you can set the field to be `DEFAULT NOT NULL` and add a `UNIQUE` constraint.
If you run here the first command again, it will display the code snippet for it:
```
bin/cake add_exposed_field PluginName.ModelName
```
You don't need the dry-run part here anymore, since it will just output the migration content in this case.

### Superimposition
In some cases you don't want to modify all public actions and their templates.
In that case you can use the superimpose functionality to map UUIDs to the primary key field on read, and the other way around on write.

For this load the `Expose.Superimpose` component in the controllers you want this, and only for those actions that are needed:
```php
// in the initialize() method
$config = [
    'actions' => [
        'index',
        'view',
    ],
];
$this->loadComponent('Expose.Superimpose', $config);
```

The behavior takes sure that you can also continue use `->id` access inside templates. With this it will superimpose that field with the UUID.
The actual primary key value will be stored in `_id` property by default here.

If you want more control over the behavior, you can disable `autoFinder` and manually set your `find('superimpose')` where needed, including associations.
