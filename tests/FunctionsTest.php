<?php
declare(strict_types=1);

namespace KEINOS\KVSQLite3;

use \KEINOS\Tests\TestCase;

final class FunctionSayHelloToTest extends TestCase
{
    public function testKVSQLite3()
    {
        $this->assertSame('Hello, World!', sayHelloTo('World'));
    }

    public function testHelloMiku()
    {
        $this->assertSame('Hello, Miku!', sayHelloTo('Miku'));
    }
}
