# CakePHP Expose plugin

[![CI](https://github.com/dereuromark/cakephp-expose/workflows/CI/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-expose/actions?query=workflow%3ACI+branch%3Amaster)
[![Codecov](https://img.shields.io/codecov/c/github/dereuromark/cakephp-expose/master.svg)](https://codecov.io/gh/dereuromark/cakephp-expose)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-expose/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-expose/license.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-expose/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

Exposes UUIDs as public identifiers for your entities instead of numeric AIID (Auto Increment ID) primary keys.

This branch is for use with **CakePHP 4.2+**. For details see [version map](https://github.com/dereuromark/cakephp-expose/wiki#cakephp-version-map).

## Key Goals
Cloaking/Obfuscation
- True randomness, so you cannot determine order or count of records per time-frame.

Security
- Mass assignment and marshalling does not allow setting this exposed field - it are hidden by default just as the primary key.

Robustness
- Must work with also more complex queries and use cases, including the atomic `updateAll()`, `deleteAll()`.
- Speed should be similar to default approach.

Simplicity
- Code changes from AIID exposure to UUID lookup should be minimal for all public endpoints.
- The default shortener provided makes the UUIDs also only 22 chars long concise strings.

## Why AIID and UUID as combination?
See [Motivation](docs/Motivation.md) for details.

## Demo
See [sandbox](https://sandbox4.dereuromark.de/sandbox/expose-examples) examples.

## Installation

You can install this plugin into your CakePHP application using [Composer](https://getcomposer.org/).

The recommended way to install is:

```
composer require dereuromark/cakephp-expose
```

Then load the plugin with the following command:
```
bin/cake plugin load Expose
```

## Usage

See [Docs](/docs) for details.

### Quick Start for adding to existing records

Faster than the speed of light:

- Add the behavior and run `bin/cake add_exposed_field PluginName.ModelName {MigrationName}` to generate a migration for adding the field.
- Execute the migration and then populate existing records using `bin/cake populate_exposed_field PluginName.ModelName`
- Re-run `bin/cake add_exposed_field PluginName.ModelName {MigrationName}` to get a non-nullable field migration for your new field.
- After also executing that migration all new records will automatically have their exposed field stored as well.

You are done and can now adjust your public actions to query by exposed field only and hide the primary key completely.
Using `Superimpose` behavior on top of `Expose` means that you actually might not even have to modify any code.
Should work out of the box.

More migration tips in [Migrating](docs/Migrating.md) section.
