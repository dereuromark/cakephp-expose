# Choosing identifiers

Expose keeps integer primary keys for internal relations and adds a UUID for public lookup.
This avoids changing every foreign key just to replace numeric IDs in URLs.
The additional field needs storage and an index; benchmark your own workload if lookup performance matters.

## UUIDv4

The default CakePHP UUID generator produces UUIDv4 identifiers.
They have no embedded timestamp or sequential record counter, so the identifier alone does not reveal
creation order. Protect private records with authorization regardless of the identifier format.

Time-ordered formats such as UUIDv7 carry timestamp information. Use them when that tradeoff fits your application.
To change generation, register a compatible UUID database type that overrides `newId()` and use it for the exposed field.
The built-in converters can shorten an existing UUID without changing its version.

Hashids and Sqids encode a numeric value. Expose instead stores an independent public identifier.
NanoID generates random strings and is a different approach from reversibly shortening a stored UUID.
