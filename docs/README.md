### Expose Plugin Documentation

### Set Up

Make sure you set  up your entity with a UUID field for exposure.
By default it is expected to be named `uuid`.
You can configure it to be any other key.

The primary key `id` (auto increment integer) will not be touched by this plugin/behavior.

#### New Table Migration
```php
$table->addColumn('uuid', 'uuid', [
    'default' => null,
    'null' => false, // Add it as true for existing entities first, then fill/populate, then set to false afterwards.
]);

// Besides primary key we will also want to have a unique index for quicker lookup on this exposed lookup field.
$table->addIndex(['uuid'], ['unique' => true]);

$table->create();
```

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

#### Adding the Behavior
In your Table class, add this in your `initialize()` method:
```php
public function initialize(array $config): void {
    parent::initialize($config);

    ...
    $this->addBehavior('Expose.Expose');
}
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

#### Pagination restrictions
Activate component for this.

//TODO

#### Using a different UUID generator

It uses UUID version 4 generator from CakePHP Text utility library by default.
If you want to use a different generator, you can set a closure:
```php
'generator' => function () {
    return MyUuidClass::generate(); // or alike
}
```

### Populating existing records

The behavior ships with a convenience method to be called from CLI.
So just hook in the method in some command and it will be able to populate the existing records with the missing UUID data.

$this->Users->
