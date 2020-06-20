<?php

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

use KEINOS\Tests\TestCase;

final class FunctionParrotryTest extends TestCase
{
    public function testRegularInput(): void
    {
        $expect = 'World';
        $actual = parrotry('World');

        $this->assertSame($expect, $actual);
    }
}
