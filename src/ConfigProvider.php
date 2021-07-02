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
                [
                    'id' => 'migration',
                    'description' => 'The migration file for settings.',
                    'source' => __DIR__ . '/../publish/2021_07_02_143511_create_settings_table.php',
                    'destination' => BASE_PATH . '/migrations/2021_07_02_143511_create_settings_table.php',
                ],
            ],
        ];
    }
}
