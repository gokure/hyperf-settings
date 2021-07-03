<?php

declare(strict_types=1);

namespace Gokure\Settings\Tests\Units;

use Gokure\Settings\SettingManager;
use Gokure\Settings\Store\FileSystemStore;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    protected $store;
    /**
     * @var Container|m\LegacyMockInterface|m\MockInterface
     */
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
        $this->store = $this->container->get(SettingManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testHelperWithoutParametersReturnsSettingManager()
    {
        $this->assertInstanceOf(SettingManager::class, setting());
    }

    public function testSingleParameterGetAKeyFromStore()
    {
        $this->store->shouldReceive('get')->with('foo', null)->once();

        $foo = setting('foo');
        $this->assertEquals(null, $foo);
    }

    public function testTwoParametersReturnADefaultValue()
    {
        $this->store->shouldReceive('get')->with('foo', 'bar')->once();

        setting('foo', 'bar');
        $this->assertTrue(true);
    }


    public function testArrayParameterCallSetMethodIntoStore()
    {
        $this->store->shouldReceive('set')->with(['foo', 'bar'])->once();

        setting(['foo', 'bar']);
        $this->assertTrue(true);
    }

    protected function getContainer()
    {
        $container = m::mock(Container::class);
        $config = new Config([
            'settings' => [
                'default' => [
                    'driver' => FileSystemStore::class,
                    'path' => dirname(__DIR__).'/tmp/settings.json',
                ],
            ],
        ]);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('has')->with(SettingManager::class)->andReturn(true);
        $container->shouldReceive('get')->with(SettingManager::class)->andReturn(new SettingManager($config));
        $container->shouldReceive('make')->with(FileSystemStore::class, m::any())->andReturn(m::mock(FileSystemStore::class));

        ApplicationContext::setContainer($container);

        return $container;
    }
}
