# Existing records

Keep the existing primary key and foreign keys. Add a second UUID column for public lookup.
Attach `Expose.Expose` to a real table class before running the plugin's commands.

## Add a nullable field

Preview the migration:

```sh
bin/cake expose add_field Articles -d
```

Answer `yes` when asked whether the table already contains records.
Remove `-d` to write the migration into `config/Migrations`:

```sh
bin/cake expose add_field Articles
bin/cake migrations migrate
```

The migration adds a nullable UUID field and a unique index. Existing rows need to be populated
before the field can become non-nullable. Ensure the migration directory exists and is writable.

## Populate missing identifiers

```sh
bin/cake expose populate_field Articles
```

The command fills rows whose exposed field is `NULL`; it preserves existing identifiers.
It works in batches of 1,000 and can be run again when new missing values need to be filled.
It does not treat empty strings as missing identifiers.

## Require the field

```sh
bin/cake expose add_field Articles
```

When the existing column is nullable and no rows remain unpopulated, this command prints a
`changeColumn()` snippet. Put that snippet into a new migration and run `bin/cake migrations migrate`.
It does not create the second migration file automatically.

For plugin tables, use `PluginName.Articles`. An optional second argument names the generated
migration class, such as `AddArticlePublicUuid`.

## Binary storage

Use `-b` when generating the first migration for a binary UUID column:

```sh
bin/cake expose add_field Articles -b -d
```

The migration uses `binary` with a 16-byte limit. In the ORM, the field must use `binaryuuid`
or a compatible custom UUID type. Set the schema type explicitly if database introspection
returns only `binary`:

```php
$this->getSchema()->setColumnType('uuid', 'binaryuuid');
```

The later non-nullable snippet preserves binary UUID storage.

## Replacing an existing UUID primary key

The plugin does not migrate primary keys or foreign keys. Plan that change separately, with a
backup and tests for each relation. Assign integer keys, update foreign-key references, and switch
constraints before exposing a separate UUID column. Preserve old UUID values if public links must keep working.
