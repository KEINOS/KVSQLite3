<?php

/**
 * Properties to be used in KVSQLite3 Class.
 * ============================================================================
 *
 * - Properties should be pre-defined here, in alphabetical order.
 * - Varibale names should be in lower_snake_case.
 */

 declare(strict_types=1);

namespace KEINOS\KVSQLite3;

trait KVSQLite3Properties
{
    /**
     * Stores an SQLite3 object.
     *
     * @var \SQLite3|null
     */
    protected $db = null;

    /**
     * True if the databese was created in-memory.
     *
     * @var bool
     */
    protected $flag_inmemory = false;

    /**
     * @var int
     */
    protected $time_ttl_default = 0;

    /**
     * @var string
     */
    protected $name_table_default = 'table_default';

    /**
     * Path to the SQLite3 database file.
     *
     * If the value is "null" then it will be created in-memory and will be
     * disposable. If it's empty string then it will be created in temp dir
     * and will be disposable as well.
     * @var string|null
     */
    protected $path_file_db = null;
}
