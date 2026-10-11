# Configuration

## Expose behavior

Attach `Expose.Expose` with table-specific options:

```php
$this->addBehavior('Expose.Expose', [
    'field' => 'public_identifier',
    'on' => 'beforeSave',
]);
```

| Option | Default | Effect |
| --- | --- | --- |
| `field` | `uuid` | Schema column used for generation and lookups. |
| `on` | `beforeSave` | Event that generates the identifier. |

The behavior merges application `Expose` configuration into its defaults; explicit behavior options take precedence.
The field's ORM database type must implement UUID generation through `newId()`.
Use a single-column primary key for the documented workflows.

`beforeSave` generates a UUID for new entities and keeps it stable on later saves.
`beforeMarshal` generates a value when data is marshalled, including when existing records are patched.
That mode can replace an existing identifier, so keep the default when public URLs must remain stable.

Protect both the primary key and exposed field with your entity's `_accessible` rules.
The plugin does not make existing entities immune to explicit field changes.

## Superimpose behavior

| Option | Default | Effect |
| --- | --- | --- |
| `autoFinder` | `true` | Adds result superimposition automatically. |
| `recursive` | `true` | Applies the finder to eligible associations. |
| `primaryKeyField` | `_id` | Property retaining the original internal key. |

The component accepts these options plus `actions`, which defaults to an empty list applying to all actions.
See [Superimposition](../guide/superimposition.md) for write and association limits.

## Converter

`Expose.converter` selects the shortening converter, as a class name or a callable returning a converter.
The default is `Expose\Converter\Short`, which requires `brick/math`.
This option does not enable short UUID storage on its own; configure the column's ORM type too.
