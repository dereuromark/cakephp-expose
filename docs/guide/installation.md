# Installation

Install the plugin in your CakePHP application:

```sh
composer require dereuromark/cakephp-expose
bin/cake plugin load Expose
```

The current package requirements are in [composer.json](https://github.com/dereuromark/cakephp-expose/blob/master/composer.json).
For older CakePHP branches, see the [version map](https://github.com/dereuromark/cakephp-expose/wiki#cakephp-version-map).

The schema examples use CakePHP Migrations. If your application does not already have it:

```sh
composer require cakephp/migrations
bin/cake plugin load Migrations
```

Then follow [Getting started](getting-started.md) for a new table or
[Existing records](existing-records.md) for a table that already contains data.

## Optional shortener

Normal UUIDs do not need a shortening dependency. For the default converter:

```sh
composer require brick/math
```

The alternative `KeikoShort` converter needs `keiko/uuid-shortener` instead.
Configure shortening as described in [Short UUIDs](short-uuids.md).
