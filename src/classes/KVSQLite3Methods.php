<?php

/**
 *  PRIVATE Methods of KVSQLite3 Class.
 * ============================================================================
 *
 * - Method names should be in lowerCamelCase.
 * - Methods should have PHPDocs and clearly defined type hints.
 * - Place the methods in alphabetical order.
 * - About Exceptions to be thrown
 *   - InvalidArgumentException
 *       Thrown on user input error. Such as malformed argument value.
 *   - RuntimeException
 *       Thrown on errors that are unable to prevent. For example, well-formed
 *       argument given but failes because of the user environment. Such as file
 *       access permission and etc.
 *   - Exception
 *       Other exceptions other than the above.
 */

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

trait KVSQLite3Methods
{
    use KVSQLite3Properties;

    /**
     * Converts the sent TTL to seconds.
     *
     * For the details see "set" interface description at:
     *   - /src/classes/psr-16/CacheInterface.php
     *
     * @param  int|\DateInterval|null $ttl
     * @return int|null
     * @throws \InvalidArgumentException
     */
    private function convertTtlToSeconds($ttl)
    {
        if (is_null($ttl)) {
            return null; // No-expiration-time-set
        }

        if (empty($ttl)) {
            return 0;
        }

        if (is_integer($ttl)) {
            return $ttl;
        }

        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         */
        if ($ttl instanceof \DateInterval) {
            // @phpstan-ignore-next-line That gives "Cannot call method add() on DateTime|false." error
            $seconds = (int) \DateTime::createFromFormat('U', '0')->add($ttl)->format('U');
            return intval($seconds);
        }

        // @phpstan-ignore-next-line Suppress "Unreachable statement"
        throw new \InvalidArgumentException(
            sprintf(
                'Expiration date must be an integer, a DateInterval or null, "%s" given',
                \is_object($ttl) ? \get_class($ttl) : \gettype($ttl)
            )
        );
    }

    /**
     * Creates a new table into the database.
     *
     * - Table info
     *   - @row integer key_hash  Hashed vale of "key_raw" in integer as a primary key.
     *   - @row text    key_raw   User defined key/ID to store the data.
     *   - @row varchar value     Data to be stored.
     *   - @row integer expire    UNIX timestamp to treat as expired. By default 0 will
     *                            be set and meaning "no-expiration time specified".
     *                            When the value is smaller than the current UNIX time
     *                            stamp, it means that the stored data had been expired.
     *   - @row integer update    Time stamp when the data was updated.
     *   - @row integer created   Time stamp when the data was created.
     *
     * @param string $name_table Name of the table to create.
     *
     * @throws \Exception
     *    Throws an Exception on failure.
     */
    private function createTable(string $name_table): void
    {
        $queries_table = [
            'key_hash INTEGER NOT NULL PRIMARY KEY',
            'key_raw TEXT',
            'value VARCHAR',
            'expire INTEGER DEFAULT NULL'
        ];
        $query_table = implode(',', $queries_table);

        $query = "CREATE TABLE IF NOT EXISTS ${name_table}(${query_table});";
        $this->executeQuery($query);
    }

    /**
     * Decode the encoded data.
     *
     * See "encodeData()" method for details.
     *
     * @param  string $data_encoded
     * @return mixed
     */
    private function decodeData(string $data_encoded)
    {
        return unserialize(rawurldecode($data_encoded));
    }


    /**
     * Delete value from database
     *
     * @param  string $key
     * @return bool
     * @throws \RuntimeException
     *    Throws an Exception on failure.
     */
    private function deleteValueFromDB(string $key): bool
    {
        $table = $this->getNameTableDefault();
        $rowid = $this->hashKeyToRowID($key);

        $query  = "DELETE FROM ${table} WHERE rowid = ${rowid};";
        try {
            $this->executeQuery($query);
            return true;
        } catch (\Exception $e) {
            $msg = 'ERROR: Failed to delete data.' . PHP_EOL . ' - Query: ' . $query;
            $msg = $this->formatMessageExceptionError($e, $msg);
            throw new \RuntimeException($msg);
        }
    }

    /**
     * Encode the data to a safe and storable string.
     *
     * This might be an overhead using this method and there might be a better
     * solution. But I gave up thinking of escaping and all, just to be secure
     * against SQL injections. So I kept this as simple as possible.
     * Though, any PR of simple, fast and secure method is welcome!
     * For a benchmark comparison see/use: /bench/MethodEncodeDataBench.php
     *
     * @param  mixed $data_raw
     * @return string
     */
    private function encodeData($data_raw): string
    {
        return rawurlencode(serialize($data_raw));
    }

    /**
     * Execute SQL query without return but Exception.
     *
     * To get/receive the results in array, use "requestQuery()" method.
     *
     * @param  string $query
     * @return void
     * @throws \Exception
     *    Throws an Exception on failure.
     */
    private function executeQuery(string $query): void
    {
        if ($this->db === null) {
            $msg = 'Failed to open database.' . PHP_EOL
                   . '- No DB instance found.' .  PHP_EOL;
            throw new \Exception($msg);
        }

        try {
            $this->db->exec($query);
        } catch (\Exception $e) {
            $msg_append = '- Failed to execute query.' . PHP_EOL
                        . '  Query: ' . trim($query) . PHP_EOL;
            $msg_error = $this->formatMessageExceptionError($e, $msg_append);
            throw new \Exception($msg_error);
        }
    }

    /**
     * Format the catched exeption/error to be comfortable to read.
     *
     * @param  object $e          Catched object when try.
     * @param  string $msg_append Additional message to append.
     * @return string
     */
    private function formatMessageExceptionError(object $e, string $msg_append = ''): string
    {
        $msg_error  = '';
        $msg_append = trim($msg_append);
        if (method_exists($e, '__toString')) {
            $capture   = explode(':', $e->__toString(), 2);
            $msg_error = $capture[0] . PHP_EOL . '- ' . $capture[1];
        }
        $msg_append = empty($msg_append) ? '' : $msg_append . PHP_EOL;

        return trim($msg_error) . PHP_EOL . $msg_append;
    }

    /**
     * @return int
     */
    private function getLifeTimeDefault(): int
    {
        return $this->time_ttl_default;
    }

    /**
     * @return string
     */
    private function getMsgErrorInvalidKey()
    {
        return <<< 'HEREDOC'
- The key must be in:
    * UTF-8 encoding (also NOT in ISO-8859-1)
    * Characters of: /[^A-Za-z0-9_\.]+/
    * Max length of up to 64 characters
HEREDOC;
    }

    /**
     * @return string
     */
    private function getNameTableDefault(): string
    {
        return $this->name_table_default;
    }

    /**
     * Gets the raw value from "Value" column of the database.
     *
     * @param  string $key
     * @return string Encoded data with encodeData() function.
     * @throws \InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     * @throws \RuntimeException         MUST be thrown if the $key does not exist.
     */
    private function getValueFromDB(string $key): string
    {
        if (! $this->isCompliantKey($key)) {
            throw new \InvalidArgumentException('ERROR: Invalid key given.');
        }

        $table = $this->getNameTableDefault();
        $rowid = $this->hashKeyToRowID($key);

        $query = "SELECT * FROM ${table} WHERE rowid=${rowid};";
        $item  = $this->requestQuery($query);

        if (! isset($item['key_raw'])) {
            throw new \RuntimeException('ERROR: "key_raw" column missing.');
        }

        if ($item['key_raw'] !== $key) {
            $msg = 'ERROR: Hash value of the key collided.' . PHP_EOL
                   . '  - Request key: ' . $key . PHP_EOL
                   . '  - Collide key: ' . $item['key_raw'] . PHP_EOL;
            throw new \RuntimeException($msg);
        }

        if (! isset($item['value'])) {
            throw new \RuntimeException('ERROR: "value" column missing.');
        }

        if (isset($item['expire'])) {
            if ($item['expire'] < time()) {
                throw new \RuntimeException('Data had been expired. At:' . $item['expire']);
            }
        }

        return (string) $item['value']; // Encoded Data
    }

    /**
     * Converts a string into an unique 8 Byte integer.
     *
     * This method is based on the below article:
     *   - https://qiita.com/KEINOS/items/c92268386d265042ea16#tsdr (in Japanese)
     *
     * In SQLite3, searching with a rowid is twice as fast as searching with a
     * primary key value. And defining the table with a primary key as "INTEGER
     * PRIMARY KEY" the primary key becomes an "alias" of a rowid.
     * So, in this method we "hash" the key string into an integer to use it as
     * a primary key value to be equivalent to rowid.
     * Also, to prevent collision as possible, we use two different hash algo-
     * rythms and mix them to create an 8 Byte integer.
     *
     * - rowid is max 8 Byte which is 16 chars long in HEX string.
     *
     * @param  string $key  String of the primary key.
     * @return int
     */
    private function hashKeyToRowID(string $key): int
    {
        $len = 8; // Length of 4 Byte hex characters
        $hash_upper = substr(hash('md5', $key), 0, $len);
        $hash_lower = substr(hash('sha512', $key), 0, $len);

        return intVal(hexdec($hash_upper . $hash_lower));
    }

    /**
     * Verifies a string if it's compliant to the MUST key requirement of PSR-16.
     *
     * - Based on MUST requirement of PSR-16 key definition.
     *   - @ref https://www.php-fig.org/psr/psr-16/#12-definitions
     * - String of the key MUST be:
     *     A-Z, a-z, 0-9, _, and . in any order in UTF-8 encoding and a length
     *     of up to 64 characters.
     *
     * @param  string $key The unique cache key of the item.
     * @return bool
     */
    private function isCompliantKey(string $key): bool
    {
        if (strlen($key) > 64) {
            $msg_error = 'The key value is too long.' . PHP_EOL . $this->getMsgErrorInvalidKey();
            throw new \InvalidArgumentException($msg_error);
        }

        return $this->isCompliantString($key);
    }

    /**
     * Verifies if a string is only in permitted characters.
     *
     * Based on MUST requirement of PSR-16 key definition.
     * String MUST be:
     *     A-Z, a-z, 0-9, _, and . in any order in UTF-8 encoding.
     *     @ref https://www.php-fig.org/psr/psr-16/#12-definitions
     *
     * @param  string $string  The string to be checked.
     * @return bool   True if the key is compliant to the MUST key requirements. False
     *                if there was an error.
     * @throws \InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    private function isCompliantString(string $string): bool
    {
        // Characters available
        $pattern = '/[^A-Za-z0-9_\\.]/';
        // Reason to use preg_match see: /bench/FunctionIsCompliantStringBench.php
        if (preg_match($pattern, $string) !== 0) {
            $msg_error = 'The key value uses non-available characters.' . PHP_EOL . $this->getMsgErrorInvalidKey();
            throw new \InvalidArgumentException($msg_error);
        }

        return true;
    }

    /**
     * @return bool True if the DB was opened in-memory. False if opened in-file
     *              including temporary database ("$db = new \SQLite3('');")
     */
    private function isDBInMemory(): bool
    {
        return $this->flag_inmemory;
    }

    /**
     * Sets SQLite3 object into "db" property and the file path to "path_file_db" property.
     *
     * @param  string $path_file_db
     * @throws \Exception
     *    Throws an Exception from \SQLite3 Class on failure.
     */
    private function openDB(string $path_file_db): void
    {
        $this->db = new \SQLite3($path_file_db);
        $this->path_file_db = $path_file_db;
    }

    /**
     * Executes SQL query and returns the results in array.
     *
     * @param  string $query
     * @return array<int|string,int|string>
     * @throws \InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     * @throws \RuntimeException         MUST be thrown if the return value is not an array.
     */
    private function requestQuery(string $query): array
    {
        if (! isset($this->db)) {
            throw new \InvalidArgumentException('ERROR: No DB instance found.');
        }

        // @phpstan-ignore-next-line Suppress "Unreachable statement"
        $result = $this->db->query($query)->fetchArray();

        if (! is_array($result)) {
            $msg = 'ERROR: Invalid return type.' . PHP_EOL
                    . '  - Retruned type: ' . gettype($result) . PHP_EOL
                    . '  - Returned value: ' . print_r($result, true);
            throw new \RuntimeException($msg);
        }

        if (empty($result)) {
            throw new \RuntimeException('ERROR: Empty data returned.');
        }

        return $result;
    }

    /**
     * Inserts data into the database.
     *
     * If the key already exists, it will overwrite the value.For the details see
     * "set()" at: /src/classes/psr-16/CacheInterface.php
     *
     * @param  string                 $key     The key to store data.
     * @param  mixed                  $value   Int, string, array and some simple objects.
     * @param  null|int|\DateInterval $ttl     Lifetime. Null sets data as limitless lifetime.
     * @return bool
     * @throws \InvalidArgumentException
     *   MUST be thrown with a clear message on fail.
     */
    private function setValueToDB(string $key, $value, $ttl = null): bool
    {
        if (! $this->isCompliantKey($key)) {
            throw new \InvalidArgumentException('ERROR: Invalid key given.');
        }

        /**
         * @psalm-suppress PossiblyNullOperand
         */
        $expire = is_null($ttl) ? 'NULL' : time() + $this->convertTtlToSeconds($ttl);
        $table  = $this->getNameTableDefault();
        $rowid  = $this->hashKeyToRowID($key);
        $data   = $this->encodeData($value);
        $value_table = "${rowid},'${key}','${data}',${expire}";

        // Insert or update/replace.
        // Reason for not using "Upsert" see: ./bench/MethodSetValueToDbBench.php
        try {
            $query = "INSERT OR REPLACE INTO ${table} VALUES(${value_table});";
                $this->executeQuery($query);
        } catch (\Exception $e) {
            $msg = $this->formatMessageExceptionError($e, 'ERROR: Failed to insert/update data.');
            throw new \InvalidArgumentException($msg);
        }

        return true;
    }
}
