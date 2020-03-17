# CakePHP Expose plugin

[![Build Status](https://travis-ci.org/dereuromark/cakephp-expose.svg?branch=master)](https://travis-ci.org/dereuromark/cakephp-expose)
[![Codecov](https://img.shields.io/codecov/c/github/dereuromark/cakephp-expose/master.svg)](https://codecov.io/gh/dereuromark/cakephp-expose)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-expose/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-expose/license.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-expose/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

Exposes UUIDs as public identifiers for your entities instead of numeric primary keys.

This branch is for use with **CakePHP 4.0+**. For details see [version map](https://github.com/dereuromark/cakephp-expose/wiki#cakephp-version-map).

## Key Goals
Cloaking/Obfuscation
- True randomness, so you cannot determine order or count of records per time-frame.

Security
- Mass assignment and marshalling does not allow setting this exposed field - it are hidden by default just as the primary key.

Robustness
- Must work with also more complex queries and use cases, including the atomic `updateAll()`, `deleteAll()`.
- Speed should be similar to default AIID (Auto Increment ID) approach.

Simplicity
- Code changes from AIID exposure to UUID lookup should be minimal for all public endpoints.

### Why UUIDs
This replaces [Hashid](https://github.com/dereuromark/cakephp-hashid) as a more explicit approach which has several advantages:
- Instead of cloaking the actual numeric primary key we use a dedicated secondary key, which removes side effects with less trivial use cases.
- The disk space addition of the UUID column is outweighed by the simple and robust usability.
- Joins and other DB specific operations now work flawlessly as they keep using the internal primary key relation.

One does have to make small adjustments to the public actions, though. All lookup by `id` need to be replaced with `uuid`.

It wouldn't even need to be a UUID, it could be any random key of any length. But UUIDs exist and are supported out of the box here.
So it is just super convenient to use them for this.

### Why not Hash IDs (anymore)
Turns out that the auto(magic) overloading of the primary key (from int to string) is not really a solid approach.

Also:
- The cloaking is also not too secure (and can be reverse engineered), using a truly random UUID though solves this by design.
- The speed is not an issue actually if you use both AIID and UUID together. We only use the exposed field for the query conditions. All joins and internals continue to use
normal primary key relations. And we have an index on that exposed field, so it really doesn't slow things down much.

### Why AIID and UUID as combination
You might ask now: Why not only UUID as primary key?

This kills internal usability (hard to remember IDs and foreign keys), as well as usually increases DB size dramatically (as each foreign key is now also a UUID).
If you do not need this, you only make life harder this way.
The UUID in our case is only meant for external lookup. Everything inside the app should still be simple, easy and fast.

Further issues with only UUID as primary key:
- Loss of deterministic sorting (which you get for free keeping the AIID primary key) and pagination (especially with burst inputs around the same times).
- Queries are often much slower here still compared to integer AIID, especially when doing a lot of joins.

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
- Use `bin/cake add_exposed_field PluginName.ModelName {MigrationName}` to get a non nullable field migration for your new field.
- After also executing that migration all new records will automatically have their exposed field stored as well.

You are done and can now adjust your public actions to query by exposed field only and hide the primary key completely.
