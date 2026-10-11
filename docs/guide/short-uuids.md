# Short UUIDs

Shortening changes the PHP and URL representation while keeping a 16-byte UUID in the database.
It reversibly encodes an existing UUID; it does not create an unrelated identifier.
Install the optional converter dependency described in [Installation](installation.md).

## Configure one column

Map a separate ORM type in `config/bootstrap.php`:

```php
use Cake\Database\TypeFactory;
use Expose\Database\Type\ShortUuidType;

TypeFactory::map('shortuuid', ShortUuidType::class);
```

In the table's `initialize()`, assign that type to the binary UUID column:

```php
$this->getSchema()->setColumnType('uuid', 'shortuuid');
$this->addBehavior('Expose.Expose');
```

This limits the mapping to that column. Mapping `binaryuuid` globally instead changes every
column that uses that ORM type, including any binary UUID primary keys.

The default converter uses a URL-safe alphabet and produces up to 22 characters for a UUID.
Do not assume every encoded UUID has exactly the same length.
`ShortUuidType` accepts the short representation or a normal 36-character UUID for database comparisons.
Keep public links in one format, with canonical URLs or redirects when switching an existing site.

## Choose a converter

Configure the alternative converter in application configuration:

```php
'Expose' => [
    'converter' => \Expose\Converter\KeikoShort::class,
],
```

A custom converter implements `Expose\Converter\ConverterInterface` with `encode()` and `decode()`.
A callable may return a configured converter instance:

```php
use Cake\Core\Configure;
use Expose\Converter\KeikoShort;
use Keiko\Uuid\Shortener\Dictionary;

Configure::write('Expose.converter', static function () {
    return new KeikoShort(Dictionary::createAlphanumeric());
});
```

Changing the alphabet changes the public encoding, so preserve the configured converter for existing links.

To convert outside the ORM:

```php
use Expose\Converter\ConverterFactory;

$short = ConverterFactory::getConverter()->encode($uuid);
$uuid = ConverterFactory::getConverter()->decode($short);
```
