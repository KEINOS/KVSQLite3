<?php

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

use KEINOS\Tests\TestCase;

final class SetThenGetMethodsTest extends TestCase
{
    /**
     * Request data before expiration.
     *
     * @dataProvider dataProviderForFilePath
     */
    public function testGetValueAfterExpiration($path_file_temp)
    {
        $db  = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);

        $ttl   = 3;
        $value = 'bar';
        $key   = 'foo';
        $db->set($key, $value, $ttl);  // Should expire after $ttl seconds

        sleep($ttl + 1); // Let it expire

        $expect = 'default dummy value' . hash('md5', strval(microtime()));
        $actual = $db->get($key, $expect);
        $msg_error = 'If the key does not exist then it should return the default value argument.';
        $this->assertSame($expect, $actual, $msg_error);
    }

    /**
     * Request data after expired.
     *
     * @dataProvider dataProviderForFilePath
     */
    public function testGetValueBeforeExpiration($path_file_temp)
    {
        $db = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);

        $expect = hash('md5', strval(microtime()));
        $key    = hash('md5', strval(microtime()));
        $ttl = 3;
        $db->set($key, $expect, $ttl);
        $actual = $db->get($key);

        $this->assertSame($expect, $actual);
    }

    /**
     * If the key does not exist, the default value argument should be returned.
     *
     * @dataProvider dataProviderForFilePath
     */
    public function testGetValueThatDoesNotExist($path_file_temp)
    {
        $db = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);

        $key = hash('md5', microtime());
        $this->expectException(\RuntimeException::class);
        $actual = $db->get($key);
    }

    /**
     * Most basic usage.
     *
     * @dataProvider dataProviderForFilePath
     */
    public function testRegularInputOfSimpleString($path_file_temp)
    {
        $db = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);

        $key    = hash('md5', strval(microtime()));
        $expect = hash('md5', strval(microtime()));
        $db->set($key, $expect);
        $actual = $db->get($key);

        $this->assertSame($expect, $actual);
    }

    /**
     * Overwrites value
     *
     * @dataProvider dataProviderForFilePath
     */
    public function testSetSameExistingKey($path_file_temp)
    {
        $db = new \KEINOS\KVSQLite3\KVSQLite3($path_file_temp);

        $key = (string) hash('md5', microtime());

        $expect = (string) hash('md5', microtime());
        $db->set($key, $expect);
        $actual = $db->get($key);
        $this->assertSame($expect, $actual);

        // Overwrite
        $expect = (string) hash('md5', microtime());
        $db->set($key, $expect);
        $actual = $db->get($key);
        $this->assertSame($expect, $actual);
    }
}
