<?php

declare(strict_types=1);

namespace Gokure\Settings\Tests\Cases;

use Gokure\Settings\SettingManager;
use Gokure\Settings\Store\FileSystemStore;
use Hyperf\Config\Config;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Utils\ApplicationContext;
use Swoole\Runtime;

class FileSystemTest extends AbstractTestCase
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
    }

    protected function tearDown(): void
    {
        $store = make(SettingManager::class)->getDriver();
        unlink($store->getPath());
        parent::tearDown();
    }

    protected function createStore(array $data = null)
    {
        $store = make(SettingManager::class)->getDriver();

        if ($data !== null) {
            $json = $data ? json_encode($data) : '{}';

            file_put_contents($store->getPath(), $json);
        }

        return $store;
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
        $this->config->set('settings', [
            'default' => [
                'driver' => FileSystemStore::class,
                'path' => dirname(__DIR__).'/tmp/settings.json',
            ],
        ]);
    }
}
