# Getting started

After [installation](installation.md), start with a new `articles` table.
This example keeps `id` as the internal primary key and adds `uuid` for public lookup.

## Create the table

Save this as `config/Migrations/20261011000000_CreateArticles.php`:

```php
<?php
use Migrations\BaseMigration;

class CreateArticles extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('articles');
        $table->addColumn('uuid', 'uuid', ['null' => false]);
        $table->addColumn('title', 'string', ['limit' => 200]);
        $table->addIndex(['uuid'], ['unique' => true]);
        $table->create();
    }
}
```

Run the migration:

```sh
bin/cake migrations migrate
```

For an existing table, use the [nullable-field migration sequence](existing-records.md) instead.

## Attach the behavior

Create `src/Model/Table/ArticlesTable.php`:

```php
<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setDisplayField('title');
        $this->addBehavior('Expose.Expose');
    }
}
```

Create `src/Model/Entity/Article.php`:

```php
<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Article extends Entity
{
    protected array $_accessible = [
        'title' => true,
        'id' => false,
        'uuid' => false,
    ];
}
```

The entity configuration protects both identifiers from request mass assignment.
The behavior does not add these accessibility rules for you.

## Save and look up a record

Run this in an application command or another place with access to the table locator:

```php
$articles = $this->fetchTable('Articles');
$article = $articles->newEntity(['title' => 'First article']);
$articles->saveOrFail($article);

$uuid = $article->uuid;
$found = $articles->find('exposed', uuid: $uuid)->firstOrFail();
```

After saving, `$article->id` is the internal integer key and `$article->uuid` is the generated UUID.
The default generation event is `beforeSave`, so read the UUID after a successful save.
Next, use it in [public actions and links](lookups.md).
