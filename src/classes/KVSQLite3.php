<?php

/**
 *  Key Value Store with SQLite3.
 * ============================================================================
 *
 * - Driver implementation of: /src/psr-16/CacheInterface.php
 * - MUST be compatible to PHP ^7.2
 * - Private/protected methods should be implemented in: ./KVSQLite3Methods.php
 * - Place the methods in alphabetical order.
 */

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

final class KVSQLite3 implements KVSQLite3Interface
{
    use KVSQLite3Properties;
    use KVSQLite3Methods;

    private const NAME_TABLE_DEFAULTX = 'table_default';

    public function __construct(string $path_file_db = null)
    {
        try {
            if ($path_file_db === null) {
                $path_file_db = ':memory:';
                $this->flag_inmemory = true;
            }
            $this->openDB($path_file_db);
            $this->createTable($this->name_table_default);
        } catch (\Exception $e) {
            $msg = $this->formatMessageExceptionError($e, 'Failed to open database.');
            throw new \RuntimeException($msg);
        }
    }

    public function clear(): bool
    {
        if ($this->isDBInMemory()) {
            return true;
        }
        return isset($this->path_file_db);
    }

    public function delete(string $key): bool
    {
        return $this->deleteValueFromDB($key);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $result = [];
        foreach ($keys as $key => $value) {
            $result[$key] = $value;
        }
        return empty($result);
    }

    public function get(string $key, $default = null)
    {
        try {
            try {
                $data_encoded = $this->getValueFromDB($key);
                return $this->decodeData($data_encoded);
            } catch (\InvalidArgumentException $e) {
                $msg_error = $this->formatMessageExceptionError($e);
                throw new \InvalidArgumentException($msg_error);
            }
        } catch (\RuntimeException $e) {
            if (is_null($default)) {
                $msg_error = $this->formatMessageExceptionError($e);
                throw new \RuntimeException($msg_error);
            }
            return $default;
        }
    }

    public function getMultiple(iterable $keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }

    public function has(string $key): bool
    {
        return empty($key);
    }

    public function set(string $key, $value, $ttl = null): bool
    {
        return $this->setValueToDB($key, $value, $ttl);
    }

    public function setMultiple(iterable $values, $ttl = null): bool
    {
        $result = [];
        foreach ($values as $key => $value) {
            $result[$key] = $value;
        }

        return empty($result);
    }

    public function parrotry(string $name): string
    {
        return parrotry($name);
    }
}
