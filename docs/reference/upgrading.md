# Upgrading

This branch supports CakePHP 5. Check [composer.json](https://github.com/dereuromark/cakephp-expose/blob/master/composer.json)
for the package's current requirements.

Use named finder arguments, such as `find('exposed', uuid: $uuid)` and
`find('exposedList', valueField: 'title')`. The exposed finder still accepts CakePHP's legacy option-array invocation.

The default generation event is `beforeSave`. Older instructions claiming that it generates identifiers
before marshalling by default are incorrect. Protect `id` and `uuid` in your entity configuration.

The non-nullable migration helper preserves an existing binary UUID field rather than changing it to a textual UUID.
Custom population conditions only select missing identifiers; populated rows are preserved.
Superimposition supports UUID `IN` conditions, integer internal keys, and null comparisons.

For CakePHP 6, review its release and migration notes when selecting a compatible plugin branch.
The current branch does not promise CakePHP 6 compatibility.
