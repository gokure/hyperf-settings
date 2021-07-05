<?php

declare(strict_types=1);

namespace Gokure\Settings\Tests\Units;

use Gokure\Settings\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    /**
     * @dataProvider provideGetData
     */
    public function testGetReturnsExpectedValue(array $data, $key, $expected): void
    {
        $this->assertEquals($expected, Arr::get($data, $key));
    }

    public function provideGetData(): array
    {
        return [
            // $data, $key, $expected
            [[], 'foo', null],
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => 'bar'], 'bar', null],
            [['foo' => 'bar'], 'foo.bar', null],
            [['foo' => ['bar' => 'baz']], 'foo.bar', 'baz'],
            [['foo' => ['bar' => 'baz']], 'foo.baz', null],
            [['foo' => ['bar' => 'baz']], 'foo', ['bar' => 'baz']],
            [
                ['foo' => 'bar', 'bar' => 'baz'],
                ['foo', 'bar'],
                ['foo' => 'bar', 'bar' => 'baz']
            ],
            [
                ['foo' => ['bar' => 'baz'], 'bar' => 'baz'],
                ['foo.bar', 'bar'],
                ['foo' => ['bar' => 'baz'], 'bar' => 'baz'],
            ],
            [
                ['foo' => ['bar' => 'baz'], 'bar' => 'baz'],
                ['foo.bar'],
                ['foo' => ['bar' => 'baz']],
            ],
            [
                ['foo' => ['bar' => 'baz'], 'bar' => 'baz'],
                ['foo.bar', 'baz'],
                ['foo' => ['bar' => 'baz'], 'baz' => null],
            ],
        ];
    }

    /**
     * @dataProvider provideGetWithDefaultsData
     */
    public function testGetWithDefaultsReturnsExpectedValue(array $data, $key, $default, $expected): void
    {
        $this->assertEquals($expected, Arr::get($data, $key, $default));
    }

    public function provideGetWithDefaultsData(): array
    {
        return [
            // $data, $key, $default, $expected
            [[], 'foo', 'default', 'default'],
            [['foo' => 'value'], 'foo', 'default', 'value'],
            [[], ['foo'], ['foo' => 'default'], ['foo' => 'default']],
            [[], ['foo'], 'default', ['foo' => null]],
            [['foo' => 'value'], ['foo'], ['foo' => 'default'], ['foo' => 'value']],
        ];
    }

    /**
     * @dataProvider provideSetData
     */
    public function testSeKeyToExpectedValue(array $input, $key, $value, array $expected): void
    {
        Arr::set($input, $key, $value);
        $this->assertEquals($expected, $input);
    }

    public function provideSetData(): array
    {
        return [
            // $input, $key, $value, $expected
            [
                ['foo' => 'bar'],
                'foo',
                'baz',
                ['foo' => 'baz'],
            ],
            [
                [],
                'foo',
                'bar',
                ['foo' => 'bar'],
            ],
            [
                [],
                'foo.bar',
                'baz',
                ['foo' => ['bar' => 'baz']],
            ],
            [
                ['foo' => ['bar' => 'baz']],
                'foo.baz',
                'foo',
                ['foo' => ['bar' => 'baz', 'baz' => 'foo']],
            ],
            [
                ['foo' => ['bar' => 'baz']],
                'foo.baz.bar',
                'baz',
                ['foo' => ['bar' => 'baz', 'baz' => ['bar' => 'baz']]],
            ],
            [
                [],
                'foo.bar.baz',
                'foo',
                ['foo' => ['bar' => ['baz' => 'foo']]],
            ],
        ];
    }

    public function testSetThrowsExceptionOnNonArraySegment(): void
    {
        $data = ['foo' => 'bar'];
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Non-array segment encountered');
        Arr::set($data, 'foo.bar', 'baz');
    }

    /**
     * @dataProvider provideHasData
     */
    public function testHasReturnsExpectedValue(array $input, $key, $expected): void
    {
        $this->assertEquals($expected, Arr::has($input, $key));
    }

    public function provideHasData(): array
    {
        return [
            // $input, $key, $expected
            [[], 'foo', false],
            [['foo' => 'bar'], 'foo', true],
            [['foo' => 'bar'], 'bar', false],
            [['foo' => 'bar'], 'foo.bar', false],
            [['foo' => ['bar' => 'baz']], 'foo.bar', true],
            [['foo' => ['bar' => 'baz']], 'foo.baz', false],
            [['foo' => ['bar' => 'baz']], 'foo', true],
            [['foo' => null], 'foo', true],
            [['foo' => ['bar' => null]], 'foo.bar', true],
        ];
    }
}
