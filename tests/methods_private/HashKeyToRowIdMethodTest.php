<?php

declare(strict_types=1);

namespace KEINOS\Tests;

final class HashKeyToRowIdMethodTestTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->obj = new \KEINOS\KVSQLite3\KVSQLite3();

        $reflection = new \ReflectionClass($this->obj);
        $this->method = $reflection->getMethod('hashKeyToRowID');

        // Give access permission to "hashKeyToRowID()"
        $this->method->setAccessible(true);
    }

    public function testRegularInput()
    {
        $data = 'sample';
        $expect = 6813939360936091722;
        $actual = $this->method->invoke($this->obj, $data);
        $this->assertSame($expect, $actual);
    }
}
