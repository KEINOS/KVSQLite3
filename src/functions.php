<?php
/**
 * Functions.
 *
 * Place the functions in alphabetical order by function name.
 */
declare(strict_types=1);

namespace KEINOS\KVSQLite3;

function sayHelloTo(string $name): string
{
    return "Hello, ${name}!";
}
