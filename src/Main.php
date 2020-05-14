<?php
/**
 * Main script.
 *
 * Overly-Cautious Development of Hello World!.
 *
 * @standard PSR2
 */
declare(strict_types=1);

namespace KEINOS\KVSQLite3;

require_once __DIR__ . '/../vendor/autoload.php';

use KEINOS\KVSQLite3\Hello;

$hello = new Hello();
$name  = 'KEINOS';

echo $hello->to($name) . PHP_EOL;
