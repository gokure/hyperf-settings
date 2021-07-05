<?php

declare(strict_types=1);

namespace Gokure\Settings\Tests\Units;

use Gokure\Settings\SettingManager;
use Gokure\Settings\DatabaseStore;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Utils\ApplicationContext;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DatabaseStoreTest extends TestCase
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

    public function testCorrectDataIsInsertedAndUpdated(): void
    {
        $query = $this->container->get(Builder::class);

        $query->shouldReceive('get')->once()->andReturn(collect([
            ['key' => 'nest.one', 'value' => 'old'],
        ]));
        $query->shouldReceive('pluck')->atMost()->andReturn(['nest.one']);
        $dbData = $this->getDbData();
        unset($dbData[1]); // remove the nest.one array member
        $query->shouldReceive('where')->with('key', '=', 'nest.one')->andReturn($query)->getMock()
            ->shouldReceive('update')->with(['value' => 'nestone']);
        $query->shouldReceive('insert')->once()->andReturnUsing(function ($arg) use ($dbData) {
            $this->assertEquals(count($dbData), count($arg));
            foreach ($dbData as $key => $value) {
                $this->assertContains($value, $arg);
            }
        });

        $store = $this->container->get(SettingManager::class)->getDriver();
        $store->set('foo', 'bar');
        $store->set('nest.one', 'nestone');
        $store->set('nest.two', 'nesttwo');
        $store->set('array', ['one', 'two']);
        $store->save();
    }

    public function testExtraColumnsAreQueried(): void
    {
        $query = $this->container->get(Builder::class);
        $query->shouldReceive('where')->once()->with('foo', '=', 'bar')
            ->andReturn($query)->getMock()
            ->shouldReceive('get')->once()->andReturn(collect([
                ['key' => 'foo', 'value' => 'bar'],
            ]));

        $store = $this->container->get(SettingManager::class)->getDriver();
        $store->setExtraColumns(['foo' => 'bar']);
        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testExtraColumnsAreInserted(): void
    {
        $query = $this->container->get(Builder::class);
        $query->shouldReceive('where')->times(1)->with('extracol', '=', 'extradata')->andReturn($query);
        $query->shouldReceive('get')->once()->andReturn(collect());
        $query->shouldReceive('pluck')->atMost()->andReturn(collect());
        $query->shouldReceive('insert')->once()->with([
            ['key' => 'foo', 'value' => 'bar', 'extracol' => 'extradata'],
        ]);

        $store = $this->container->get(SettingManager::class)->getDriver();
        $store->set('foo', 'bar');
        $store->setExtraColumns(['extracol' => 'extradata']);
        $store->save();
        $this->assertTrue(true);
    }

    protected function getDbData(): array
    {
        return [
            ['key' => 'foo', 'value' => 'bar'],
            ['key' => 'nest.one', 'value' => 'nestone'],
            ['key' => 'nest.two', 'value' => 'nesttwo'],
            ['key' => 'array.0', 'value' => 'one'],
            ['key' => 'array.1', 'value' => 'two'],
        ];
    }

    protected function getContainer(): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with(Builder::class)->andReturn($query = m::mock(Builder::class));
        $container->shouldReceive('get')->with(ConnectionInterface::class)->andReturn($connection = m::mock(ConnectionInterface::class));
        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver = m::mock(ConnectionResolverInterface::class));
        $resolver->shouldReceive('connection')->andReturn($connection);
        $connection->shouldReceive('table')->andReturn($query);

        $config = new Config([
            'settings' => [
                'default' => [
                    'driver' => DatabaseStore::class,
                    'database' => [
                        'connection' => null,
                        'table' => 'settings',
                        'key_column' => 'key',
                        'value_column' => 'value',
                    ],
                ],
            ],
        ]);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        $container->shouldReceive('get')->with(SettingManager::class)->andReturn(new SettingManager($config));
        $container->shouldReceive('make')->with(DatabaseStore::class, m::any())->andReturnUsing(function ($_, $args) use ($container) {
            return new DatabaseStore($container, $args['config']);
        });

        ApplicationContext::setContainer($container);

        return $container;
    }
}
