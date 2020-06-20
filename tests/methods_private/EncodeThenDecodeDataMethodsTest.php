<?php

declare(strict_types=1);

namespace KEINOS\Tests;

final class EncodeThenDecodeDataMethodsTest extends TestCase
{
    /**
     * @var \KEINOS\KVSQLite3\KVSQLite3
     */
    private $obj;

    /**
     * @var \ReflectionMethod
     */
    private $encodeData;

    /**
     * @var \ReflectionMethod
     */
    private $decodeData;

    public function setUp(): void
    {
        parent::setUp();

        $this->obj = new \KEINOS\KVSQLite3\KVSQLite3();

        // Create reflection object to test private methods
        $reflection = new \ReflectionClass($this->obj);
        $this->encodeData = $reflection->getMethod('encodeData');
        $this->decodeData = $reflection->getMethod('decodeData');

        // Give access permission to the private methods
        $this->encodeData->setAccessible(true);
        $this->decodeData->setAccessible(true);
    }

    public function testArrayInput()
    {
        $expect = [
            'sample1',
            'sample2',
        ];

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }

    public function testBooleanInput()
    {
        $expect = true;

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);

        $expect = false;

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }

    public function testFloatInput()
    {
        $expect = 1 / 3;
        $this->assertTrue(is_float($expect));

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }

    public function testIntegerInput()
    {
        // Binary
        $expect = 0b11111111; // 255

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);

        // Decimal
        $expect = 12345;

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);

        // Octal
        $expect = 0123; // 83

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);

        // Hexadecimal
        $expect = 0x1A; // 26

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);

        // Large Number that overflows in 32 bit (will treat as float)
        $million = 1000000;
        $expect  = 50000 * $million;

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);

        // Large Number that overflows in 64 bit (will treat as float)
        $million = 1000000;
        $expect  = 50000000000000 * $million;

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }

    public function testNullInput()
    {
        $expect = null;
        $this->assertTrue(is_null($expect));

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }

    public function testSimpleIterableObjectInput()
    {
        // Simple JSON object
        $obj = json_encode([
            'sample1',
            'sample2',
        ]);

        // Iterable
        $expect = [
            $obj,
            $obj,
        ];

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }

    public function testSimpleObjectInput()
    {
        // Simple JSON object
        $expect = json_encode([
            'sample1',
            'sample2',
        ]);

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }

    public function testStringInput()
    {
        $expect  = 'sample';

        $encoded = $this->encodeData->invoke($this->obj, $expect);
        $actual  = $this->decodeData->invoke($this->obj, $encoded);

        $this->assertSame($expect, $actual);
    }
}
