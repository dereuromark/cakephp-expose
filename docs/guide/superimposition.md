# Superimposition

Prefer explicit UUID lookups when you can change the public actions.
The optional `Superimpose` component helps selected actions keep using `$entity->id` and `get($id)`.

Load it in the controller's `initialize()` after `parent::initialize()`:

```php
$this->loadComponent('Expose.Superimpose', [
    'actions' => ['index', 'view'],
]);
```

The controller's default table must already have `Expose.Expose` attached.
For the selected actions, the component attaches `Expose.Superimpose` to that table.
An empty `actions` list applies it to every action, so use an explicit list for public endpoints.

## Read behavior

Returned records expose the UUID in `id`; the original primary key is retained in `_id`.
String primary-key conditions in the main query's `where` and `having` clauses are resolved through the exposed UUID field. Nested queries keep their own table conditions.
Integer lookup values remain internal primary keys so ORM existence rules can work. Keep public route arguments as UUID strings: casting them to integers enables internal-ID lookup. Apply authorization checks regardless of the identifier. UUID lists use the exposed field directly with `IN` or `NOT IN`; unknown identifiers do not abort the whole query.

Use explicit exposed-field conditions for range comparisons or bulk operations rather than relying on primary-key rewriting.

With `autoFinder` enabled, the behavior adds the `superimpose` finder to queries.
Set `autoFinder` to `false` to apply `find('superimpose')` manually.
An explicit query option `superimpose: false` skips the automatic query rewriting.

## Associations and writes

The default `recursive` option adds superimposition to associated tables with the Expose behavior
when their association finder is `all`. This changes the association configuration for subsequent queries
using those table objects during the request.

Existing records are restored to their internal key before entity saves and deletes, and saves expose
the UUID again afterwards. The plugin does not translate every foreign-key assignment in nested writes.
Keep complex write operations on the ordinary integer-key tables, or use `recursive: false` and test
those operations against your own associations.

Internal primary-key integer validation can conflict with a superimposed UUID.
Avoid applying this behavior to admin actions that need internal IDs.
