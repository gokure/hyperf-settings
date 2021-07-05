<?php

declare(strict_types=1);

return [
    'default' => [
        /**
         * Default Store Driver
         *
         * This option controls the default setting connection that gets used while
         * using this setting library.
         *
         * Supported: `FileSystemStore` and `DatabaseStore`
         */
        'driver' => Gokure\Settings\FileSystemStore::class,

        /**
         * FileSystem Store
         *
         * This option used when the driver is `FileSystemStore`, and make sure the path
         * is writable.
         */
        'path' => BASE_PATH . '/runtime/settings.json',

        /**
         * Database Store
         *
         * This option used when the driver is DatabaseStore.
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
