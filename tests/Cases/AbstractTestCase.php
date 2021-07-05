<?php

declare(strict_types=1);

namespace Gokure\Settings\Tests\Cases;

use Gokure\Settings\DatabaseStore;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    abstract protected function createStore(array $data = null);

    protected function getStore(array $data = null)
    {
        return $this->createStore($data);
    }

    protected function assertStoreEquals($store, $expected, $message = ''): void
    {
        $this->assertEquals($expected, $store->all(), $message);
        $store->save();
        $store = $this->getStore();
        $this->assertEquals($expected, $store->all(), $message);
    }

    protected function assertStoreKeyEquals($store, $key, $expected, $message = ''): void
    {
        $this->assertEquals($expected, $store->get($key), $message);
        $store->save();
        $store = $this->getStore();
        $this->assertEquals($expected, $store->get($key), $message);
    }

    public function testStoreIsEmpty(): void
    {
        $store = $this->getStore();
        $this->assertEquals([], $store->all());
    }

    public function testGetKeyWithDefault(): void
    {
        $store = $this->getStore();
        $this->assertEquals('default', $store->get('foo', 'default'));
    }

    public function testGetKeysWithDefaults(): void
    {
        $store = $this->getStore();
        $store->set('foo', 'bar');
        $this->assertEquals(['foo' => 'bar', 'bar' => 'default'], $store->get(['foo', 'bar'], ['foo' => 'default', 'bar' => 'default']));
    }

    public function testSetKey(): void
    {
        $store = $this->getStore();
        $store->set('foo', 'bar');
        $this->assertStoreKeyEquals($store, 'foo', 'bar');
    }

    public function testSetNestedKeys(): void
    {
        $store = $this->getStore();
        $store->set('foo.bar', 'baz');
        $this->assertStoreEquals($store, ['foo' => ['bar' => 'baz']]);
    }

    public function testCannotSetNestedKeyOnNonArrays(): void
    {
        $this->expectException('UnexpectedValueException');

        $store = $this->getStore();
        $store->set('foo', 'bar');
        $store->set('foo.bar', 'baz');
    }

    public function testForgetKey(): void
    {
        $store = $this->getStore();
        $store->set('foo', 'bar');
        $store->set('bar', 'baz');
        $this->assertStoreEquals($store, ['foo' => 'bar', 'bar' => 'baz']);

        $store->forget('foo');
        $this->assertStoreEquals($store, ['bar' => 'baz']);
    }

    public function testForgetNestedKey(): void
    {
        $store = $this->getStore();
        $store->set('foo.bar', 'baz');
        $store->set('foo.baz', 'bar');
        $store->set('bar.foo', 'baz');
        $this->assertStoreEquals($store, [
            'foo' => [
                'bar' => 'baz',
                'baz' => 'bar',
            ],
            'bar' => [
                'foo' => 'baz',
            ],
        ]);

        $store->forget('foo.bar');
        $this->assertStoreEquals($store, [
            'foo' => [
                'baz' => 'bar',
            ],
            'bar' => [
                'foo' => 'baz',
            ],
        ]);

        $store->forget('bar.foo');
        $expected = [
            'foo' => [
                'baz' => 'bar',
            ],
            'bar' => [
            ],
        ];
        if ($store instanceof DatabaseStore) {
            unset($expected['bar']);
        }
        $this->assertStoreEquals($store, $expected);
    }

    public function testFlush(): void
    {
        $store = $this->getStore(['foo' => 'bar']);
        $this->assertStoreEquals($store, ['foo' => 'bar']);
        $store->flush();
        $this->assertStoreEquals($store, []);
    }
}
