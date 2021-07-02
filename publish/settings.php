<?php

declare(strict_types=1);

return [
    'default' => [
        'driver' => Gokure\Settings\Store\FileSystemStore::class,

        'path' => BASE_PATH . '/runtime/settings.json',

        'database' => [
            'connection' => null,
            'table' => 'settings',
            'key_column' => 'key',
            'value_column' => 'value',
        ],
    ],
];
