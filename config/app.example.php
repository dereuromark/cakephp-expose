<?php

/**
 * Expose Example Configuration
 *
 * Merge the keys below into your application's config/app.php (or
 * config/app_local.php) — do not replace the whole file, since this snippet
 * only contains this plugin's configuration. When copying entries that
 * reference imported classes, use fully-qualified class names or move the
 * `use` imports to the top of the target file. Customize the values as needed.
 *
 * The `Expose` namespace is read by Expose\Model\Behavior\ExposeBehavior (merged as
 * config defaults for every table using the behavior) and `Expose.converter` is read by
 * Expose\Converter\ConverterFactory. Per-table behavior options still override these.
 */
return [
	'Expose' => [
		// The exposed field name (the public, non-incremental identifier column).
		// Default: 'uuid'.
		'field' => 'uuid',

		// When the exposed value is generated. Default: 'beforeSave'.
		'on' => 'beforeSave',

		// Converter for encoding/decoding exposed values. Accepts a class string
		// implementing Expose\Converter\ConverterInterface, or a callable returning such an
		// instance. Available converters: Expose\Converter\Short (default) and
		// Expose\Converter\KeikoShort. When null, ConverterFactory falls back to Short.
		// Default: not set (Short).
		'converter' => \Expose\Converter\Short::class,
	],
];
