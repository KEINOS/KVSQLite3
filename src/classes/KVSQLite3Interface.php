<?php

/**
 * KVSQLite3 Interface.
 * ============================================================================
 *
 * Defines the "public" methods that should be implemented in KVSQLite3 class
 * other than in "PSR-16 Simple Cache Interface".
 *   - See: /src/psr-16/CacheInterface.php
 *
 * - Private/protected methods should be defined in: KVSQLite3Methods.php
 * - Method names should be in lowerCamelCase.
 * - Place the methods in alphabetical order.
 */

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

interface KVSQLite3Interface extends \Psr\SimpleCache\CacheInterface
{
    /**
     * Instantiates an KVSQLite3 object and opens an SQLite3 database.
     *
     * @param null|string $path_file_db File path of the SQLite3 file to be saved.
     *
     * If the file path is not provided then the database will be opened
     * "in-memory" and will be disposable.
     * If the argument is an empty string then the database will be opened in file
     * under temp directory and will be disposable as well.
     * In these cases, user should dump to a file to make the database be permanent
     * before closing the script.
     *
     * @suppress PhanTypeInvalidThrowsIsInterface
     * @throws \RuntimeException
     *   \RuntimeException MUST be thrown in any case of instantiation failure.
     *   Such as failing to open or create the database and etc.
     */
    public function __construct(string $path_file_db = null);
}
