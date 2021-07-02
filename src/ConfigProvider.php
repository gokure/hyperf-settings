<?php

declare(strict_types=1);

namespace Gokure\Settings;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file for settings.',
                    'source' => __DIR__ . '/../publish/settings.php',
                    'destination' => BASE_PATH . '/config/autoload/settings.php',
                ],
            ],
        ];
    }
}
