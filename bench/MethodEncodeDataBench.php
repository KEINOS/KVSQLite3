<?php
/**
 * Test to compare speed between implementation of "encodeData()" method.
 *
 * @BeforeMethods({"setUp"})
 */
class MethodEncodeDataBench
{
    public function setUp()
    {
        // List of characters to use
        $this->list_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.';

        // Generate string to test
        $len_str = 64;
        $len_list_chars = strlen($this->list_chars);
        $str_random = '';
        for ($i = 0; $i < $len_str; $i++) {
            $pos = \mt_rand() % $len_list_chars;
            $str_random .= $this->list_chars[$pos];
        }

        // Set test string
        $this->str_random = $str_random;
    }

    public function benchOnGoingMethod()
    {
        // Encode
        $encoded = rawurlencode(serialize($this->str_random));
        // Decode
        $decoded = unserialize(rawurldecode($encoded));

        // Compare
        return ($this->str_random === $decoded);
    }

}

/*
-------------------------------------------------------------------------------
 Bench result
-------------------------------------------------------------------------------

# composer bench ./bench/MethodEncodeDataBench.php
> Composer\Config::disableProcessTimeout
> ./vendor/bin/phpbench run --revs=1000000 --iterations=10 --report=compare --report=aggregate --warmup=2 --progress=histogram './bench/MethodEncodeDataBench.php'
PhpBench @git_tag@. Running benchmarks.


\MethodEncodeDataBench (#0 benchOnGoingMethod)

#0  (σ = 0.865μs ) -2σ [   ▄ ███▄▄       ] +2σ [μ Mo]/r: 56.470 56.210 μRSD/r: 1.53%

1 subjects, 10 iterations, 1,000,000 revs, 0 rejects, 0 failures, 0 warnings
(best [mean mode] worst) = 55.537 [56.470 56.210] 58.877 (μs)v(s),
⅀T: 564.697μs μSD/r 0.865μs μRSD/r: 1.532%

+-----------------------+--------------------+-----+---------+-----------------------------------------------------+
| benchmark             | subject            | set | revs    | suite:1343c4bff71cbbe832f000bd0476e35b8c8eff35:mean |
+-----------------------+--------------------+-----+---------+-----------------------------------------------------+
| MethodEncodeDataBench | benchOnGoingMethod | 0   | 1000000 | 56.470μs                                            |
+-----------------------+--------------------+-----+---------+-----------------------------------------------------+

suite: 1343c4bff71cbbe832f000bd0476e35b8c8eff35, date: 2020-05-23, stime: 11:15:47
+-----------------------+--------------------+-----+---------+-----+----------+----------+----------+----------+----------+---------+--------+-------+
| benchmark             | subject            | set | revs    | its | mem_peak | best     | mean     | mode     | worst    | stdev   | rstdev | diff  |
+-----------------------+--------------------+-----+---------+-----+----------+----------+----------+----------+----------+---------+--------+-------+
| MethodEncodeDataBench | benchOnGoingMethod | 0   | 1000000 | 10  | 471,472b | 55.537μs | 56.470μs | 56.210μs | 58.877μs | 0.865μs | 1.53%  | 1.00x |
+-----------------------+--------------------+-----+---------+-----+----------+----------+----------+----------+----------+---------+--------+-------+

*/
