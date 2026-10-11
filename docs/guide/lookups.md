# Public lookups

Use the `exposed` finder wherever a public route receives a UUID:

```php
public function view(string $uuid): void
{
    $article = $this->fetchTable('Articles')
        ->find('exposed', uuid: $uuid)
        ->firstOrFail();

    $this->set(compact('article'));
}
```

In the template:

```php
<?= $this->Html->link($article->title, ['action' => 'view', $article->uuid]) ?>
```

`firstOrFail()` returns a not-found error when the UUID has no matching record.
Apply your application's ownership and authorization checks before displaying private data.
A UUID does not grant access by itself.

## Select lists

`exposedList` uses the exposed UUID as the key and the table's display field as the value:

```php
$choices = $articles->find('exposedList', valueField: 'title')->toArray();
```

It also accepts `keyField`, `groupField`, and `valueSeparator`, following CakePHP's list finder.

## Custom queries

Use an aliased field name when joins could introduce another `uuid` column:

```php
$field = $articles->getBehavior('Expose')->getExposedKey(true);
$article = $articles->find()->where([$field => $uuid])->firstOrFail();
```

`updateAll()` and `deleteAll()` do not run entity save callbacks. Generate identifiers explicitly
for inserts that bypass normal entity saves, and supply conditions for bulk updates or deletes.

Use a pagination `sortableFields` allowlist if the public interface should hide internal ID ordering.
