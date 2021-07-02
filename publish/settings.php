<?php

declare(strict_types=1);

return [
    'default' => [
        'driver' => Gokure\Settings\Store\FileSystemStore::class,

        'path' => BASE_PATH . '/runtime/settings.json',
    ],
];
