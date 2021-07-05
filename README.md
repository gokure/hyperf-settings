# Persistent settings package for Hyperf

[![Build Status](https://www.travis-ci.com/gokure/hyperf-settings.svg?branch=main)](https://www.travis-ci.com/gokure/hyperf-settings)
[![Latest Stable Version](https://poser.pugx.org/gokure/hyperf-settings/v/stable)](https://packagist.org/packages/gokure/hyperf-settings)
[![Total Downloads](https://poser.pugx.org/gokure/hyperf-settings/downloads)](https://packagist.org/packages/gokure/hyperf-settings)
[![License](https://poser.pugx.org/akaunting/laravel-setting/license.svg)](LICENSE)

This package allows you to save settings in a more persistent way. You can use the database and/or json file to save your settings.

## Installation

1. Require the `gokure/hypref-settings` package in your `composer.json` and update your dependencies:

```sh
composer require gokure/hypref-settings
```

2. Publish the config and migration files:

```sh
php bin/hyperf.php vendor:publish gokure/hypref-settings
```

## Usage

```php
$store = $container->get(\Gokure\Settings\SettingManager::class)->getDriver();

$store->set('foo', 'bar');
$store->get('foo', 'default value');
$store->get('nested.element');
$store->forget('foo');
$stores = $store->all();
```

Call `$store->save()` explicitly to save changes made.

You can also to use the `setting()` helper:

```php
// Get the `default` store instance
setting();

// Get values
setting('foo');
setting('foo.bar');
setting('foo', 'default value');
setting()->get('foo');

// Set values
setting(['foo' => 'bar']);
setting(['foo.bar' => 'baz']);
setting()->set('foo', 'bar');

// Method chaining
setting(['foo' => 'bar'])->save();
```

### Configuration

You can change `config/autoload/settings.php`

```php
return [
    'default' => [
        /**
         * Default Store Driver
         *
         * This option controls the default setting connection that gets used while
         * using this setting library.
         *
         * Supported: `FileSystemStore::class` and `DatabaseStore::class`
         */
        'driver' => Gokure\Settings\FileSystemStore::class,

        /**
         * FileSystem Store
         *
         * This option used when the driver is set `FileSystemStore::class`, and
         * make sure the path is writable.
         */
        'path' => BASE_PATH . '/runtime/settings.json',

        /**
         * Database Store
         *
         * This option used when the driver is set DatabaseStore::class.
         */
        'database' => [
            // If set to null, the default connection will be used.
            'connection' => null,
            // Table name.
            'table' => 'settings',
            // Column names in database store.
            'key_column' => 'key',
            'value_column' => 'value',
        ],
    ],
];
```

### Auto Saving

if you add the middleware `Gokure\Settings\SaveMiddleware` to your `middlewares` list in `config/autoload/middlewares.php`, settings will be saved automatically at the end of all HTTP requests, but you'll still need to call `$store->save()` explicitly in commands, queue workers etc.

### FileSystem Store

You can modify the path used on run-time using `$store->setPath($path)`.

### Database Store

If you want to use the database as settings storage then you should run the `php bin/hyperf.php migrate`. You can modify the table fields from the `create_settings_table` file in the migrations directory.

#### Extra Columns

If you want to store settings for multiple users/clients in the same database you can do so by specifying extra columns:

```php
$store->setExtraColumns(['user_id' => 1]);
```

where `user_id = 1` will now be added to the database query when settings are retrieved, and when new settings are saved, the `user_id` will be populated.

If you need more fine-tuned control over which data gets queried, you can use the `setConstraint` method which takes a closure with two arguments:

- `$query` is the query builder instance
- `$insert` is a boolean telling you whether the query is an insert or not. If it is an insert, you usually don't need to do anything to `$query`.

```php
$store->setConstraint(function($query, $insert) {
	if (!$insert) {
	    $query->where(/* ... */);
	}
});
```

## License

Released under the MIT License, see [LICENSE](LICENSE).
