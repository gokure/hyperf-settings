<?php

declare(strict_types=1);

namespace Gokure\Settings\Tests\Cases;

use Gokure\Settings\SettingManager;
use Gokure\Settings\Store\DatabaseStore;
use Gokure\Settings\Tests\Stubs\CreateSettingsTableStub;
use Hyperf\Config\Config;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Utils\ApplicationContext;
use Swoole\Runtime;

class DatabaseTest extends AbstractTestCase
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
        $this->config = $this->container->get(ConfigInterface::class);
        $this->initConfig();
        $this->initTable();
    }

    protected function tearDown(): void
    {
        $db = $this->config->get('settings.default.database');
        $cmd = new CreateSettingsTableStub($db['table'], $db['key_column'], $db['value_column']);
        $cmd->down();
        parent::tearDown();
    }

    protected function createStore(array $data = null)
    {
        if ($data !== null) {
            $store = $this->createStore();
            $store->set($data);
            $store->save();
            unset($store);
        }

        return make(SettingManager::class)->getDriver();
    }

    protected function getContainer(): Container
    {
        Runtime::enableCoroutine(true);
        $container = new Container((new DefinitionSourceFactory(true))());
        $container->set(ConfigInterface::class, new Config([]));
        $container->get(ApplicationInterface::class);

        ApplicationContext::setContainer($container);

        return $container;
    }

    protected function initConfig(): void
    {
        $this->config->set('databases', [
            'default' => [
                'driver' => env('DB_DRIVER', 'mysql'),
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'hyperf'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => env('DB_PREFIX', ''),
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 20,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 5.0,
                    'heartbeat' => -1,
                    'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
                ],
            ],
        ]);

        $this->config->set('settings', [
            'default' => [
                'driver' => DatabaseStore::class,
                'database' => [
                    'connection' => null,
                    'table' => 'settings',
                    'key_column' => 'key',
                    'value_column' => 'value',
                ],
            ],
        ]);
    }

    protected function initTable()
    {
        $db = $this->config->get('settings.default.database');
        $cmd = new CreateSettingsTableStub($db['table'], $db['key_column'], $db['value_column']);
        $cmd->up();
    }
}
