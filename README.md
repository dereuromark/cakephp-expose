# CakePHP Expose plugin

[![Build Status](https://travis-ci.org/dereuromark/cakephp-expose.svg?branch=master)](https://travis-ci.org/dereuromark/cakephp-expose)
[![Codecov](https://img.shields.io/codecov/c/github/dereuromark/cakephp-expose/master.svg)](https://codecov.io/gh/dereuromark/cakephp-expose)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-expose/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-expose/license.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-expose/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

Exposes UUIDs as public identifiers for your entities instead of numeric primary keys.

## Key Goals
Cloaking/Obfuscation
- True randomness, so you cannot determine order or count of records per time-frame.

Security
- Mass assignment and marshalling does not allow setting this exposed field - it are hidden by default just as the primary key.

Robustness
- Must work with also more complex queries and use cases, including the atomic `updateAll()`, `deleteAll()`.
- Speed should be similar to default AIID approach.

Simplicity
- Code changes from AIID exposure to UUID lookup should be minimal for all public endpoints.

### Why UUIDs
This replaces [Hashid](https://github.com/dereuromark/cakephp-hashid) as a more explicit approach which has several advantages:
- Instead of cloaking the actual numeric primary key we use a dedicated secondary key, which removes side effects with less trivial use cases.
- The disk space addition of the UUID column is outweighed by the simple and robust usability.
- Joins and other DB specific operations now work flawlessly as they keep using the internal primary key relation.

One does have to make small adjustments to the public actions, though. All lookup by `id` need to be replaced with `uuid`.

### Why not Hash IDs (anymore)
Turns out that the auto(magic) overloading of the primary key (from int to string) is not really a solid approach.
Also:
- The cloaking is also not too secure (and can be reverse engineered), using a truly random UUID though solves this by design.
- The speed is not an issue actually if you use both AIID and UUID together. We only use the exposed field for the query conditions. All joins and internals continue to use
normal primary key relations. And we have an index on that exposed field, so it really doesn't slow things down much.

## Installation

You can install this plugin into your CakePHP application using [Composer](https://getcomposer.org/).

The recommended way to install is:

```
composer require dereuromark/cakephp-expose:dev-master
```

Then load the plugin with the following command:
```
bin/cake plugin load Expose
```

## Usage

See [Docs](/docs) for details.

## TODOs

- Confirm UUID binary16 compatibility/usage instead of default char36. Saves some disk space, should still be as fast.
