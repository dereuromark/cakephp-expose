# Upgrading

This development branch prepares finder contracts for CakePHP 6. Its current dependency constraints still use CakePHP 5 for verification. Check [composer.json](https://github.com/dereuromark/cakephp-expose/blob/master/composer.json)
for the package's current requirements.

Use named finder arguments, such as `find('exposed', uuid: $uuid)` and
`find('exposedList', valueField: 'title')`. The exposed finder still accepts CakePHP's legacy option-array invocation, with CakePHP's deprecation warning. Use named arguments for new code.

The default generation event is `beforeSave`. Older instructions claiming that it generates identifiers
before marshalling by default are incorrect. Protect `id` and `uuid` in your entity configuration.

The non-nullable migration helper preserves an existing binary UUID field rather than changing it to a textual UUID.
Custom population conditions only select missing identifiers; populated rows are preserved.
Superimposition supports UUID `IN` and `NOT IN` conditions without failing on unknown UUIDs. Integer internal keys remain available for ORM existence rules; public actions should accept UUID strings.

For CakePHP 6, review its release and migration notes when selecting a compatible plugin branch.
The current branch does not promise CakePHP 6 compatibility.

## Finder contracts prepared for CakePHP 6

Update direct `findExposedList()` calls to named arguments such as `keyField: 'id'` and `valueField: 'title'`. The options-array method signature is removed. The default list key is the configured exposed field, including when `keyField` is null; pass `keyField: 'id'` to select internal primary keys.

`findSuperimpose()` now takes only its query. Subclass finders must match the new signatures. The protected `resolve(string $uuid)` hook now declares `string|int` as its return type; add that declaration to existing overrides. Stringable UUID comparison values and SQL subqueries remain supported.
