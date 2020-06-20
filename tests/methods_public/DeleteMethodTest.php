<?php

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

use KEINOS\Tests\TestCase;

final class DeleteMethodTest extends TestCase
{
    /**
     * Delete expired data.
     *
     * @dataProvider dataProviderForFilePath
     */
    public function testDeleteExpiredData($path_file_temp)
    {
        $db = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);

        $key   = hash('md5', strval(microtime()));
        $value = hash('md5', strval(microtime()));
        $ttl   = 3;
        $db->set($key, $value, $ttl);  // Should expire in $ttl seconds

        sleep($ttl + 1); // Let it expire

        $db->delete($key);

        // Default message (2nd arg in "get()") set
        $expect = 'default dummy return value' . hash('md5', strval(microtime()));
        $actual = $db->get($key, $expect);
        $msg_error  = 'If the key does not exist default value in arg should return.';
        $this->assertSame($expect, $actual, $msg_error);

        // No default message (2nd arg in "get()") set
        $msg_error = 'If the key does not exist and default value is not set a RuntimeException shold be thrown.';
        $this->expectException(\RuntimeException::class);
        $actual = $db->get($key);
    }

    /**
     * Typical deletion of the data.
     *
     * @dataProvider dataProviderForFilePath
     */
    public function testDeleteNormaly($path_file_temp)
    {
        $db  = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);
        $key = hash('md5', strval(microtime()));

        // Set data
        $expect = hash('md5', strval(microtime()));
        $db->set($key, $expect);
        // Double check before delete
        $actual = $db->get($key);
        $this->assertSame($expect, $actual, 'Failed to set data before deletion.');

        // Delete
        $db->delete($key);

        // 2nd arg set
        $expect = 'default dummy return value' . hash('md5', strval(microtime()));
        $actual = $db->get($key, $expect);
        $msg_error = 'If the key does not exist default value in arg should return.';
        $this->assertSame($expect, $actual, $msg_error);

        // 2nd arg not set (Use default return value. Such as "null")
        $msg_error = 'If the key does not exist and default value is not set a RuntimeException shold be thrown.';
        $this->expectException(\RuntimeException::class);
        $actual = $db->get($key);
    }
}
