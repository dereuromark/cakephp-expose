# CakePHP Expose plugin

[![CI](https://github.com/dereuromark/cakephp-expose/actions/workflows/ci.yml/badge.svg?branch=cake6)](https://github.com/dereuromark/cakephp-expose/actions/workflows/ci.yml?query=branch%3Acake6)
[![Codecov](https://img.shields.io/codecov/c/github/dereuromark/cakephp-expose/master.svg)](https://codecov.io/gh/dereuromark/cakephp-expose)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-expose/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.5-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-expose/license.svg)](LICENSE)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-expose/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-expose)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-purple.svg?style=flat-square)](https://github.com/php-fig-rectified/fig-rectified-standards)

Use UUIDs as public identifiers while keeping integer primary keys for internal relations.
The default UUIDv4 generator does not encode record creation time or order.
Your application controls entity accessibility and authorization.

Read the [documentation website](https://dereuromark.github.io/cakephp-expose/):

- [Installation](https://dereuromark.github.io/cakephp-expose/guide/installation)
- [Getting started](https://dereuromark.github.io/cakephp-expose/guide/getting-started)
- [Existing records](https://dereuromark.github.io/cakephp-expose/guide/existing-records)
- [Choosing identifiers](https://dereuromark.github.io/cakephp-expose/guide/motivation)

Package requirements are maintained in [composer.json](composer.json).
This development branch targets CakePHP 6 and PHP 8.5.

For older CakePHP branches, see the [version map](https://github.com/dereuromark/cakephp-expose/wiki#cakephp-version-map).

## Example

```php
$articles->addBehavior('Expose.Expose');
$article = $articles->newEntity(['title' => 'First article']);
$articles->saveOrFail($article);

$found = $articles->find('exposed', uuid: $article->uuid)->firstOrFail();
```

Add a UUID schema column and protect its entity accessibility first, as shown in Getting started.
The UUID is generated on saving a new entity; the ordinary primary key remains available internally.

[Sandbox examples](https://sandbox4.dereuromark.de/sandbox/expose-examples) show public lookups in an application.
