# Upgrading

This development branch targets CakePHP 6 and PHP 8.5. Check [composer.json](https://github.com/dereuromark/cakephp-expose/blob/cake6/composer.json)
for the package's current requirements.

Use named finder arguments, such as `find('exposed', uuid: $uuid)` and
`find('exposedList', valueField: 'title')`.

The default generation event is `beforeSave`. Older instructions claiming that it generates identifiers
before marshalling by default are incorrect. Protect `id` and `uuid` in your entity configuration.

The non-nullable migration helper preserves an existing binary UUID field rather than changing it to a textual UUID.
Custom population conditions only select missing identifiers; populated rows are preserved.
Superimposition supports UUID `IN` and `NOT IN` conditions without failing on unknown UUIDs. Integer internal keys remain available for ORM existence rules; public actions should accept UUID strings.

CakePHP 6 is still a development dependency. Review its migration notes before upgrading an application.

## CakePHP 6 finder contracts

Update direct `findExposedList()` calls to named arguments such as `keyField: 'id'` and `valueField: 'title'`. The options-array method signature is removed. The default list key is the configured exposed field, including when `keyField` is null; pass `keyField: 'id'` to select internal primary keys. Pass list options directly to the finder; query-level `keyField` options are no longer inherited.

`findSuperimpose()` now takes only its query. Subclass finders must match the new signatures. The protected `resolve(string $uuid)` hook now declares `string|int` as its return type; add that declaration to existing overrides. Stringable UUID values remain supported in scalar equality comparisons. SQL subqueries remain supported.

## Framework changes

Custom behavior and component configuration defaults now use `$defaultConfig`. Entity mass-assignment rules use `$patchable`, and marshalling options use `patchableFields`. The behavior still permits its generated UUID when configured for `beforeMarshal`.

Commands implement parameterless `execute()` and use the command's argument and console services. Existing `expose add_field` and `expose populate_field` command options remain available.

CakePHP 6 removes the table's behavior method proxy. Call `$table->getBehavior('Expose')->getExposedKey()` and `$table->getBehavior('Expose')->initExposedField()` on the behavior instance.
