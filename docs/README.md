## Expose Plugin Documentation

### Set Up

We will now add a new UUID field to your entity for exposure.
By default it is expected to be named `uuid`.
You can configure it to be any other key.

The primary key `id` (auto-increment integer) will not be touched by this plugin/behavior.

By default this exposed field is also expected to be of a UUID type (either binary16/32 or char36 etc), recognized by CakePHP as such.
If you plan on using a custom length/UUID-type (e.g. varchar64), then you need to make sure the mapping to a valid UUID type class happens in your configuration.

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
Now let's make sure you set up your new entity with this new field, ideally including it in the migration.

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
If you want to save disk space, you can also use binary UUID (16 instead of 32 char length):
```php
$table->addColumn('uuid', 'binary', [
    'limit' => 16,
    ...
```
With 16 instead of 36 byte you save half of the disk storage needed, and long term this definitely can be a lot.
As such this is the recommended type. On top you can later add shortening, for details see further down.


#### Existing Table Migration
If you already have a table, and you want to add it to this existing one:

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

Tip: Binary UUID saves a lot of disk space in the long run. Use `-b`/`--binary` option here.

Then execute the migration using `bin/cake migrations migrate`.

#### Entity update
You want to make sure that neither primary key, nor this exposed field is patchable (when marshalling = mass assignment):
```php
protected array $_accessible = [
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
->where([..., $field => $value])
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
If you want to use a different generator, you need to create a custom database type extending the `UuidType`
and overwrite the `newId()` method:
```php
public function newId(): string {
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

Once all records are populated, you can make a second migration file and set the field
to be `DEFAULT NOT NULL` and add a `UNIQUE` constraint.
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

#### Saving records
With superimposed primary keys, saving - and in particularly updating - records becomes a bit more complicated.
Here, the behavior tries to auto-convert the primary keys back before saving.
What it currently cannot fully do yet is to take care of relations and their foreign keys. Do not activate this behavior
for those cases (yet). The primary intend of Superimpose is still to make exposure more easy and convenient - as readonly lookups.

Tip: Make sure you don't have any validation or domain rules on the primary key (e.g. "integer"). Those would work against the behavior here.

If you want still want to partially use it on saving nested entities, you can set `recursive` option to false on the behavior.
Then all relations will just use `Expose` - and you will just have to use the `uuid` field on those for follow up usage in that same request.


#### UUID Shortening
If you also want to shorten the resulting output UUID from 32 to 22 chars, go with `ShortUuidType` class which extends the binary one.
Make sure that your `uuid` is set to the binary type as outlined above.

While Migrations/Phinx require this field type to be `binary` length 16, the schema in CakeCHP ORM itself represents this as `binaryuuid` type.
So here we need to overwrite this type-mapping of CakePHP now.

In this case you need to specify the type-map in your bootstrap:
```php
use Cake\Database\TypeFactory;

TypeFactory::map('binaryuuid', \Expose\Database\Type\ShortUuidType::class);
```
A UUID stored in the DB as `4e52c919-513e-4562-9248-7dd612c6c1ca` would then be displayed and
used as `fpfyRTmt6XeE9ehEKZ5LwF` for example.
This can be a bit more user-friendly as it is shorter and selectable with a double click in most browsers and apps.

Make sure to include the required composer dependencies as listed at the top of the converter class.
For the default one to work you need to run
```
composer require brick/math
```

You can select a different converter than the default `Short` one by configuring it through `Expose.converter`:
```php
// e.g. in your app.php
'Expose.converter' => \Expose\Converter\KeikoShort::class,
```
Or write your own one on plugin or app level using the given interface:
```php
namespace App\Converter;

use Expose\Converter\ConverterInterface;

class MyShort implements ConverterInterface {
    // implement the methods
}
```

You can even provide your own callable if needed for a constructor initialization:
```php
'Expose.converter' => function () {
    return new KeikoShort(Dictionary::createAlphanumeric());
},
```

Note: Adding a shortener later on is BC in terms of accessibility.
The record can still always also be accessed through the long 36-char string here.
Just make sure you 301 SEO-redirect or use canonical linking if that is relevant for you and those records.

If you need to access your converter anywhere in your system, you use the factory:
```php
use Expose\Converter\ConverterFactory;

$result = ConverterFactory::getConverter()->encode($value);
$result = ConverterFactory::getConverter()->decode($value);
```

### Backend
The plugin comes with an optional and small admin backend to reverse UUIDs.
This can come in handy sometimes.

Browse to `/admin/expose` for this.
As long as you didn't disable the routes for the plugin it should be visible right away.

If you have Auth enabled, make sure to allow your user (or admin role) access.
Using [TinyAuth](https://github.com/dereuromark/cakephp-tinyauth) plugin, for example (in `auth_acl.ini`):
```ini
[Expose.Admin/Expose]
* = admin
```
