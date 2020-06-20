<?php

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

use KEINOS\Tests\TestCase;

final class ConstructionTest extends TestCase
{
    /**
     * @dataProvider dataProviderForFilePath
     */
    public function testSimpleInstantiation($path_file_temp)
    {
        $db = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);
        $expect = 'KEINOS\KVSQLite3\KVSQLite3';
        $actual = get_class($db);

        $this->assertSame($expect, $actual);
    }

    public function testDesignateAMalformedFileAsDbFile()
    {
        $path_file_temp = $this->getPathFileTemp();

        $handle = fopen($path_file_temp, "w");
        fwrite($handle, "writing a dummy data to temp file");
        fclose($handle);

        $this->expectException(\RuntimeException::class);
        $this->obj = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);
    }
}
