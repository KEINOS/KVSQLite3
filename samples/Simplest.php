<?php

/**
 * Sample script of KVSQLite3 usage.
 *
 * To run this script in VSCode, press F5 and see the debug console.
 */

declare(strict_types=1);

namespace KEINOS\Sample;

require_once __DIR__ . '/../vendor/autoload.php';

use KEINOS\KVSQLite3\KVSQLite3;

try {
    $db = new KVSQLite3();
    $db->set('foo', 'bar');

    echo 'Expect: bar', PHP_EOL;
    echo 'Actual: ', $db->get('foo'), PHP_EOL;
} catch (\Exception $e) {
    $msg = 'ERROR:' . $e->getMessage() . PHP_EOL;
    //throw new \RuntimeException($msg);
    echo $msg;
}
