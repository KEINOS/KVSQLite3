<?php
/**
 * Bench to determine which algorithm/method pattern to use in "isCompliantString()" method.
 *
 * - Each method must comply the below:
 *   - Available list_chars: "A-Z" "a-z","0-9" "_" and "."
 *
 * - To run this test, use:
 *   - $ composer bench ./bench/FunctionIsCompliantStringBench.php
 *
 * - Current conclusion:
 *   - Currently we decided to use: benchPregMatch
 *
 * - Reason:
 *   - In some cases "ctype_alnum()" was faster than "preg_match()". Running locally on macOS
 *     for example. But running on Docker and different versions of PHP, "preg_match()" was
 *     always stable. Also "ctype_alnum()" has some locale dependant issue.
 *   - Comparison between preg_match() and ctype_alnum():
 *       https://gist.github.com/KEINOS/61b4d1bec18696713a726079592dc919 @ Gist
 *
 * @BeforeClassMethods({"initData"})
 * @BeforeMethods({"setUp"})
 */
class MethodIsCompliantStringBench
{
    // List of characters available
    private const LIST_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.';
    private const PATTERN = '/[^A-Za-z0-9_\\.]/';

    public function benchCtypeAlnum()
    {
        $subject = $this->str_random;
        $list_white_chars = array('_', '.');

        return ctype_alnum(str_replace($list_white_chars, '', $subject));
    }

    public function benchPregMatch()
    {
        $pattern = self::PATTERN;
        $subject = $this->str_random;

        if (preg_match($pattern, $subject) === 0) {
            return true;
        }
        return false;
    }

    public function benchStrspn()
    {
        $mask    = self::LIST_CHARS;
        $subject = $this->str_random;

        if (strspn($subject, $mask) !== strlen($subject)) {
            return false;
        }
        return true;
    }

    public static function getPathFileData()
    {
        $name_file_data = 'PHPBench-' . hash('md5', __FILE__);

        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name_file_data;
    }

    /**
     * Use the same random value in all test methods.
     *
     * @return void
     */
    public static function initData()
    {
        $path_file_data = self::getPathFileData();

        if (! file_exists($path_file_data)) {
            // Generate a random string to test
            $len_str = 64;
            $len_list_chars = strlen(self::LIST_CHARS);
            $str_random = '';
            for ($i = 0; $i < $len_str; $i++) {
                $pos = \mt_rand() % $len_list_chars;
                $str_random .= self::LIST_CHARS[$pos];
            }
            file_put_contents($path_file_data, $str_random);
        }
    }

    /**
     * Use the same random value in all test methods.
     *
     * @return void
     */
    public function setUp()
    {
        $path_file_data = self::getPathFileData();
        $this->str_random = file_get_contents($path_file_data);
    }
}

/*
-------------------------------------------------------------------------------
 Bench result (PHP 7.1.33, macOS local run)
-------------------------------------------------------------------------------
$ composer bench ./bench/MethodIsCompliantStringBench.php
> Composer\Config::disableProcessTimeout
> ./vendor/bin/phpbench run --revs=1000000 --iterations=10 --report=compare --report=aggregate --warmup=2 --progress=histogram --php-config='{"memory_limit": "1G"}' './bench/MethodIsCompliantStringBench.php'
PhpBench @git_tag@. Running benchmarks.


\MethodIsCompliantStringBench (#0 benchCtypeAlnum, #1 benchPregMatch, #2 benchStrspn)

#0  (σ = 0.005μs ) -2σ [   ▄▄▄██▄    ▄   ] +2σ [μ Mo]/r: 0.248 0.246 μRSD/r: 2.20%
#1  (σ = 0.051μs ) -2σ [     ▆█▂ ▂       ] +2σ [μ Mo]/r: 1.714 1.697 μRSD/r: 3.00%
#2  (σ = 0.008μs ) -2σ [  █▄ ▄ ▄ ▄▄█  ▄  ] +2σ [μ Mo]/r: 1.800 1.806 μRSD/r: 0.47%

3 subjects, 30 iterations, 3,000,000 revs, 0 rejects, 0 failures, 0 warnings
(best [mean mode] worst) = 0.241 [1.254 1.249] 0.260 (μs)),
⅀T: 37.619μs μSD/r 0.022μs μRSD/r: 1.892%

+------------------------------+-----------------+-----+---------+-----------------------------------------------------+
| benchmark                    | subject         | set | revs    | suite:1343ca089d05f9590e84c693478376205a19a0cc:mean |
+------------------------------+-----------------+-----+---------+-----------------------------------------------------+
| MethodIsCompliantStringBench | benchCtypeAlnum | 0   | 1000000 | 0.248μs                                             |
| MethodIsCompliantStringBench | benchPregMatch  | 0   | 1000000 | 1.714μs                                             |
| MethodIsCompliantStringBench | benchStrspn     | 0   | 1000000 | 1.800μs                                             |
+------------------------------+-----------------+-----+---------+-----------------------------------------------------+

suite: 1343ca089d05f9590e84c693478376205a19a0cc, date: 2020-06-08, stime: 08:24:08
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+
| benchmark                    | subject         | set | revs    | its | mem_peak | best    | mean    | mode    | worst   | stdev   | rstdev | diff  |
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+
| MethodIsCompliantStringBench | benchCtypeAlnum | 0   | 1000000 | 10  | 427,384b | 0.241μs | 0.248μs | 0.246μs | 0.260μs | 0.005μs | 2.20%  | 1.00x |
| MethodIsCompliantStringBench | benchPregMatch  | 0   | 1000000 | 10  | 427,384b | 1.679μs | 1.714μs | 1.697μs | 1.863μs | 0.051μs | 3.00%  | 6.92x |
| MethodIsCompliantStringBench | benchStrspn     | 0   | 1000000 | 10  | 427,384b | 1.788μs | 1.800μs | 1.806μs | 1.813μs | 0.008μs | 0.47%  | 7.26x |
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+

-------------------------------------------------------------------------------
 Bench result (PHP 7.4.6, Docker container)
-------------------------------------------------------------------------------

$ composer bench ./bench/MethodIsCompliantStringBench.php
> Composer\Config::disableProcessTimeout
> ./vendor/bin/phpbench run --revs=1000000 --iterations=10 --report=compare --report=aggregate --warmup=2 --progress=histogram --php-config='{"memory_limit": "1G"}' './bench/MethodIsCompliantStringBench.php'
PhpBench @git_tag@. Running benchmarks.


\MethodIsCompliantStringBench (#0 benchCtypeAlnum, #1 benchPregMatch, #2 benchStrspn)

#0  (σ = 0.104μs ) -2σ [  ▄▄▄ ▄█ ▄█      ] +2σ [μ Mo]/r: 2.463 2.449 μRSD/r: 4.22%
#1  (σ = 0.321μs ) -2σ [     ▆█▂  ▂      ] +2σ [μ Mo]/r: 2.154 2.030 μRSD/r: 14.92%
#2  (σ = 0.048μs ) -2σ [   ▃▅▅   █  ▃   ▃] +2σ [μ Mo]/r: 4.066 4.042 μRSD/r: 1.19%

3 subjects, 30 iterations, 3,000,000 revs, 0 rejects, 0 failures, 0 warnings
(best [mean mode] worst) = 1.932 [2.894 2.841] 2.704 (μs)),
⅀T: 86.834μs μSD/r 0.158μs μRSD/r: 6.773%

+------------------------------+-----------------+-----+---------+-----------------------------------------------------+
| benchmark                    | subject         | set | revs    | suite:1343ca0a70adf70d2b2f54afd6324ed67654846d:mean |
+------------------------------+-----------------+-----+---------+-----------------------------------------------------+
| MethodIsCompliantStringBench | benchCtypeAlnum | 0   | 1000000 | 2.463μs                                             |
| MethodIsCompliantStringBench | benchPregMatch  | 0   | 1000000 | 2.154μs                                             |
| MethodIsCompliantStringBench | benchStrspn     | 0   | 1000000 | 4.066μs                                             |
+------------------------------+-----------------+-----+---------+-----------------------------------------------------+

suite: 1343ca0a70adf70d2b2f54afd6324ed67654846d, date: 2020-06-08, stime: 08:17:56
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+
| benchmark                    | subject         | set | revs    | its | mem_peak | best    | mean    | mode    | worst   | stdev   | rstdev | diff  |
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+
| MethodIsCompliantStringBench | benchCtypeAlnum | 0   | 1000000 | 10  | 484,248b | 2.326μs | 2.463μs | 2.449μs | 2.704μs | 0.104μs | 4.22%  | 1.14x |
| MethodIsCompliantStringBench | benchPregMatch  | 0   | 1000000 | 10  | 484,248b | 1.932μs | 2.154μs | 2.030μs | 3.053μs | 0.321μs | 14.92% | 1.00x |
| MethodIsCompliantStringBench | benchStrspn     | 0   | 1000000 | 10  | 484,248b | 4.007μs | 4.066μs | 4.042μs | 4.170μs | 0.048μs | 1.19%  | 1.89x |
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+

-------------------------------------------------------------------------------
 Bench result (PHP 8.0.0-dev, Docker, KEINOS/php8-jit:latest)
-------------------------------------------------------------------------------
# composer bench ./bench/MethodIsCompliantStringBench.php
> Composer\Config::disableProcessTimeout
> ./vendor/bin/phpbench run --revs=1000000 --iterations=10 --report=compare --report=aggregate --warmup=2 --progress=histogram --php-config='{"memory_limit": "1G"}' './bench/MethodIsCompliantStringBench.php'
PhpBench @git_tag@. Running benchmarks.


\MethodIsCompliantStringBench (#0 benchCtypeAlnum, #1 benchPregMatch, #2 benchStrspn)

#0  (σ = 0.034μs ) -2σ [    ▃█▅▃▃  ▃     ] +2σ [μ Mo]/r: 1.026 1.012 μRSD/r: 3.30%
#1  (σ = 0.009μs ) -2σ [   ▃ █▃█    ▃    ] +2σ [μ Mo]/r: 0.220 0.216 μRSD/r: 4.11%
#2  (σ = 0.012μs ) -2σ [  ▄▄█  ▄ █ █  ▄  ] +2σ [μ Mo]/r: 1.881 1.873 μRSD/r: 0.66%

3 subjects, 30 iterations, 3,000,000 revs, 0 rejects, 0 failures, 0 warnings
(best [mean mode] worst) = 0.210 [1.043 1.034] 0.242 (μs)),
⅀T: 31.276μs μSD/r 0.018μs μRSD/r: 2.692%

+------------------------------+-----------------+-----+---------+-----------------------------------------------------+
| benchmark                    | subject         | set | revs    | suite:1343ca09f26dbdc85cc4dc9ea015b2678bf11181:mean |
+------------------------------+-----------------+-----+---------+-----------------------------------------------------+
| MethodIsCompliantStringBench | benchCtypeAlnum | 0   | 1000000 | 1.026μs                                             |
| MethodIsCompliantStringBench | benchPregMatch  | 0   | 1000000 | 0.220μs                                             |
| MethodIsCompliantStringBench | benchStrspn     | 0   | 1000000 | 1.881μs                                             |
+------------------------------+-----------------+-----+---------+-----------------------------------------------------+

suite: 1343ca09f26dbdc85cc4dc9ea015b2678bf11181, date: 2020-06-08, stime: 08:34:15
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+
| benchmark                    | subject         | set | revs    | its | mem_peak | best    | mean    | mode    | worst   | stdev   | rstdev | diff  |
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+
| MethodIsCompliantStringBench | benchCtypeAlnum | 0   | 1000000 | 10  | 458,696b | 1.000μs | 1.026μs | 1.012μs | 1.118μs | 0.034μs | 3.30%  | 4.67x |
| MethodIsCompliantStringBench | benchPregMatch  | 0   | 1000000 | 10  | 458,696b | 0.210μs | 0.220μs | 0.216μs | 0.242μs | 0.009μs | 4.11%  | 1.00x |
| MethodIsCompliantStringBench | benchStrspn     | 0   | 1000000 | 10  | 458,696b | 1.864μs | 1.881μs | 1.873μs | 1.903μs | 0.012μs | 0.66%  | 8.55x |
+------------------------------+-----------------+-----+---------+-----+----------+---------+---------+---------+---------+---------+--------+-------+

*/
