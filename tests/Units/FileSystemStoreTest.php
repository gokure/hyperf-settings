<?php

declare(strict_types=1);

namespace Gokure\Settings\Tests\Units;

use Gokure\Settings\SettingManager;
use Gokure\Settings\Store\FileSystemStore;
use Hyperf\Config\Config;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Filesystem\Filesystem;
use InvalidArgumentException;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FileSystemStoreTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testThrowsExceptionWhenFileNotWriteable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $files = $this->container->get(Filesystem::class);
        $files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
        $files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(false);
        $this->container->get(SettingManager::class)->getDriver();
    }

    public function testThrowsExceptionWhenFilesPutFails(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $files = $this->container->get(Filesystem::class);
        $files->shouldReceive('exists')->once()->with('fakepath')->andReturn(false);
        $files->shouldReceive('put')->once()->with('fakepath', '{}')->andReturn(false);
        $this->container->get(SettingManager::class)->getDriver();
    }

    public function testThrowsExceptionWhenFileContainsInvalidJsonFormat(): void
    {
        $this->expectException(RuntimeException::class);

        $files = $this->container->get(Filesystem::class);
        $files->shouldReceive('exists')->once()->with('fakepath')->andReturn(true);
        $files->shouldReceive('isWritable')->once()->with('fakepath')->andReturn(true);
        $files->shouldReceive('get')->once()->with('fakepath')->andReturn('[[!1!11]');
        $store = $this->container->get(SettingManager::class)->getDriver();
        $store->get('foo');
    }

    protected function getContainer(): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);
        $config = new Config([
            'settings' => [
                'default' => [
                    'driver' => FileSystemStore::class,
                    'path' => 'fakepath',
                ],
            ],
        ]);
        $container->shouldReceive('get')->with(SettingManager::class)->andReturn(new SettingManager($config));
        $container->shouldReceive('get')->with(Filesystem::class)->andReturn(m::mock(Filesystem::class));
        $container->shouldReceive('make')->with(FileSystemStore::class, m::any())->andReturnUsing(function ($_, $args) use ($container) {
            return new FileSystemStore($container, $args['config']);
        });

        ApplicationContext::setContainer($container);

        return $container;
    }
}
