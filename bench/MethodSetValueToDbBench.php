<?php
/**
 * Test to compare between "INSERT OR REPLACE INTO", disperse and " UPSERT"
 *
 * - Current conclusion:
 *   - Currently we decided to use: "insertOrReplace()" method.
 *
 * - Reason:
 *   First of all, see the bench results at the bottom. According to the bench
 *   results, "insertOrReplace()" method was the fastest. But "insertOrReplace()"
 *   method has one big problem. "INSERT OR REPLACE INTO" query overwrites all
 *   the fields, which means "time_create" field also needs to be updated. If
 *   we need to leave the "time_create" field as is, un-updated, then we have
 *   to take the "disperse()" method. Which was the slowest and most memory used.
 *   So, "upSert()" method seems to be the better choice since it's the second
 *   better bench in speed and bench.
 *
 *   But do we really need these time stamp feelds other than "expire"? Should
 *   not we keep it as simple as possible? We can let the users include the time
 *   stamp such as "time_create" and "time_update" in their "data" field, don't
 *   we?
 *
 * @BeforeClassMethods({"initData"})
 * @BeforeMethods({"setUp"})
 */
class MethodSetValueToDbBench
{
    private $db;
    private $data;
    private const ITERATION = 10;

    public function benchInsertDisperse()
    {
        $this->db = $this->createDb();
        $this->disperse();
    }

    public function benchInsertOrReplace()
    {
        $this->db = $this->createDb();
        $this->insertOrReplace();
    }

    public function benchInsertPureInsert()
    {
        $this->db = $this->createDb();
        $this->pureInsert();
    }

    public function benchInsertUpSert()
    {
        $this->db = $this->createDb();
        $this->upSert();
    }

    public function benchOverwriteDisperse()
    {
        $this->db = $this->createDb();
        for ($i=0; $i<self::ITERATION; ++$i) {
            $this->disperse();
        }
    }

    public function benchOverwriteInsertOrReplace()
    {
        $this->db = $this->createDb();
        for ($i=0; $i<self::ITERATION; ++$i) {
            $this->insertOrReplace();
        }
    }

    public function benchOverwriteUpSert()
    {
        $this->db = $this->createDb();
        for ($i=0; $i<self::ITERATION; ++$i) {
            $this->upSert();
        }
    }

    public function createDb()
    {
        if (isset($this->db)) {
            unset($this->db);
        }

        $db = new \SQLite3(':memory:');

        $name_table = 'tbl_default';
        $queries_table = [
            'key_hash INTEGER NOT NULL PRIMARY KEY',
            'key_raw TEXT',
            'value VARCHAR',
            'expire INTEGER DEFAULT NULL',
            'time_update INTEGER',
            'time_create INTEGER'
        ];
        $query_table = implode(',', $queries_table);

        $query = "CREATE TABLE IF NOT EXISTS ${name_table}(${query_table});";

        $db->exec($query);

        return $db;
    }

    public function disperse()
    {
        foreach ($this->data as $params) {
            $table = 'tbl_default';

            $rowid  = $params['rowid'];
            $key    = $params['key'];
            $data   = $params['data'];
            $expire = $params['expire'];
            $time_update = $params['time_update'];
            $time_create = $params['time_create'];

            try {
                // Try insert
                $value_table = "${rowid},'${key}','${data}',${expire},${time_update},${time_create}";
                $query = "INSERT INTO ${table} VALUES(${value_table});";
                $this->db->exec($query);
            } catch (\EXCEPTION $e) {
                // Try overwrite/update
                $name_rows  = '"key_hash","key_raw","value","expire","time_update"';
                $value_rows = "${rowid},'${key}','${data}',${expire},${time_update}";
                $query  = "REPLACE INTO ${table}(${name_rows}) VALUES(${value_rows});";
                $this->db->exec($query);
            }
        }
    }

    public static function getPathFileData()
    {
        $name_file_data = 'PHPBench-' . hash('md5', __FILE__ . self::ITERATION);

        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name_file_data;
    }

    public static function initData()
    {
        $path_file_data   = self::getPathFileData();
        $count_data_items = self::ITERATION * 1000;

        if (! file_exists($path_file_data)) {
            $time = time();

            for ($i = 1; $i < $count_data_items; ++$i) {
                $s = strval($i);
                $data[] = [
                    'rowid'  => $i,
                    'key'    => $s,
                    'data'   => 'sample',
                    'expire' => 'NULL',
                    'time_update' => $time,
                    'time_create' => $time,
                ];
            }
            $data_str = serialize($data);

            file_put_contents($path_file_data, $data_str);
        }
    }

    public function insertOrReplace()
    {
        foreach ($this->data as $params) {
            $table = 'tbl_default';

            $rowid  = $params['rowid'];
            $key    = $params['key'];
            $data   = $params['data'];
            $expire = $params['expire'];
            $time_update = $params['time_update'];
            $time_create = $params['time_create'];
            $value_table = "${rowid},'${key}','${data}',${expire},${time_update},${time_create}";

            $query = "INSERT OR REPLACE INTO ${table} VALUES(${value_table});";
            $this->db->exec($query);
        }
    }

    public function pureInsert()
    {
        foreach ($this->data as $params) {
            $table = 'tbl_default';

            $rowid  = $params['rowid'];
            $key    = $params['key'];
            $data   = $params['data'];
            $expire = $params['expire'];
            $time_update = $params['time_update'];
            $time_create = $params['time_create'];

            $value_table = "${rowid},'${key}','${data}',${expire},${time_update},${time_create}";
            $query = "INSERT INTO ${table} VALUES(${value_table});";
            $this->db->exec($query);
        }
    }

    public function setUp()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $msg  = 'Error #' . $errno . ': ';
            $msg .= $errstr . " on line " . $errline . " in file " . $errfile;
            throw new RuntimeException($msg);
        });
        $data_str   = file_get_contents(self::getPathFileData());
        $this->data = unserialize($data_str);
    }

    public function upSert()
    {
        foreach ($this->data as $params) {
            $table  = 'tbl_default';
            $rowid  = $params['rowid'];
            $key    = $params['key'];
            $data   = $params['data'];
            $expire = $params['expire'];
            $time_update = $params['time_update'];
            $time_create = $params['time_create'];
            $value_table = "${rowid},'${key}','${data}',${expire},${time_update},${time_create}";
            // Insert all or update only necessary values
            $query = "INSERT INTO ${table} VALUES(${value_table})"
                    . "ON CONFLICT(rowid) DO UPDATE SET "
                    . "value='${data}', "
                    . "expire=${expire}, "
                    . "time_update=${time_update}";
            $this->db->exec($query);
        }
    }
}

/*
-------------------------------------------------------------------------------
 Bench result (PHP 7.4.6, Docker, php:7-cli-alpine)
-------------------------------------------------------------------------------
$ composer bench ./bench/MethodSetValueToDbBench.php
> Composer\Config::disableProcessTimeout
> ./vendor/bin/phpbench run --retry-threshold=1 --revs=1000 --report=compare --report=aggregate --warmup=2 --progress=verbose --php-config='{"memory_limit": "1G"}' './bench/MethodSetValueToDbBench.php'
PhpBench @git_tag@. Running benchmarks.

\MethodSetValueToDbBench

    benchInsertDisperse.....................I0 [μ Mo]/r: 50,211.658 50,211.658 (μs) [μSD μRSD]/r: 0.000μs 0.00%
    benchInsertOrReplace....................I0 [μ Mo]/r: 62,990.583 62,990.583 (μs) [μSD μRSD]/r: 0.000μs 0.00%
    benchInsertPureInsert...................I0 [μ Mo]/r: 69,105.675 69,105.675 (μs) [μSD μRSD]/r: 0.000μs 0.00%
    benchInsertUpSert.......................I0 [μ Mo]/r: 104,156.497 104,156.497 (μs) [μSD μRSD]/r: 0.000μs 0.00%
    benchOverwriteDisperse..................I0 [μ Mo]/r: 1,510,692.517 1,510,692.517 (μs) [μSD μRSD]/r: 0.000μs 0.00%
    benchOverwriteInsertOrReplace...........I0 [μ Mo]/r: 595,193.779 595,193.779 (μs) [μSD μRSD]/r: 0.000μs 0.00%
    benchOverwriteUpSert....................I0 [μ Mo]/r: 624,472.436 624,472.436 (μs) [μSD μRSD]/r: 0.000μs 0.00%

7 subjects, 7 iterations, 7,000 revs, 0 rejects, 0 failures, 0 warnings
(best [mean mode] worst) = 50,211.658 [430,974.735 430,974.735] 50,211.658 (μs)
⅀T: 3,016,823.145μs μSD/r 0.000μs μRSD/r: 0.000%

+-------------------------+-------------------------------+-----+------+-----------------------------------------------------+
| benchmark               | subject                       | set | revs | suite:1343ca1fbf9625e7e078ce2fa3cfe913104e1b0a:mean |
+-------------------------+-------------------------------+-----+------+-----------------------------------------------------+
| MethodSetValueToDbBench | benchInsertDisperse           | 0   | 1000 | 50,211.658μs                                        |
| MethodSetValueToDbBench | benchInsertOrReplace          | 0   | 1000 | 62,990.583μs                                        |
| MethodSetValueToDbBench | benchInsertPureInsert         | 0   | 1000 | 69,105.675μs                                        |
| MethodSetValueToDbBench | benchInsertUpSert             | 0   | 1000 | 104,156.497μs                                       |
| MethodSetValueToDbBench | benchOverwriteDisperse        | 0   | 1000 | 1,510,692.517μs                                     |
| MethodSetValueToDbBench | benchOverwriteInsertOrReplace | 0   | 1000 | 595,193.779μs                                       |
| MethodSetValueToDbBench | benchOverwriteUpSert          | 0   | 1000 | 624,472.436μs                                       |
+-------------------------+-------------------------------+-----+------+-----------------------------------------------------+

suite: 1343ca1fbf9625e7e078ce2fa3cfe913104e1b0a, date: 2020-06-09, stime: 02:29:05
+-------------------------+-------------------------------+-----+------+-----+-------------+-----------------+-----------------+-----------------+-----------------+---------+--------+--------+
| benchmark               | subject                       | set | revs | its | mem_peak    | best            | mean            | mode            | worst           | stdev   | rstdev | diff   |
+-------------------------+-------------------------------+-----+------+-----+-------------+-----------------+-----------------+-----------------+-----------------+---------+--------+--------+
| MethodSetValueToDbBench | benchInsertDisperse           | 0   | 1000 | 1   | 11,701,880b | 50,211.658μs    | 50,211.658μs    | 50,211.658μs    | 50,211.658μs    | 0.000μs | 0.00%  | 1.00x  |
| MethodSetValueToDbBench | benchInsertOrReplace          | 0   | 1000 | 1   | 11,701,880b | 62,990.583μs    | 62,990.583μs    | 62,990.583μs    | 62,990.583μs    | 0.000μs | 0.00%  | 1.25x  |
| MethodSetValueToDbBench | benchInsertPureInsert         | 0   | 1000 | 1   | 11,701,880b | 69,105.675μs    | 69,105.675μs    | 69,105.675μs    | 69,105.675μs    | 0.000μs | 0.00%  | 1.38x  |
| MethodSetValueToDbBench | benchInsertUpSert             | 0   | 1000 | 1   | 11,701,880b | 104,156.497μs   | 104,156.497μs   | 104,156.497μs   | 104,156.497μs   | 0.000μs | 0.00%  | 2.07x  |
| MethodSetValueToDbBench | benchOverwriteDisperse        | 0   | 1000 | 1   | 52,233,648b | 1,510,692.517μs | 1,510,692.517μs | 1,510,692.517μs | 1,510,692.517μs | 0.000μs | 0.00%  | 30.09x |
| MethodSetValueToDbBench | benchOverwriteInsertOrReplace | 0   | 1000 | 1   | 11,701,888b | 595,193.779μs   | 595,193.779μs   | 595,193.779μs   | 595,193.779μs   | 0.000μs | 0.00%  | 11.85x |
| MethodSetValueToDbBench | benchOverwriteUpSert          | 0   | 1000 | 1   | 11,701,880b | 624,472.436μs   | 624,472.436μs   | 624,472.436μs   | 624,472.436μs   | 0.000μs | 0.00%  | 12.44x |
+-------------------------+-------------------------------+-----+------+-----+-------------+-----------------+-----------------+-----------------+-----------------+---------+--------+--------+

*/
