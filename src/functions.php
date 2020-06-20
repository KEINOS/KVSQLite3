<?php

/**
 * Functions.
 * ============================================================================
 *
 * - Place the functions in alphabetical order.
 */

declare(strict_types=1);

namespace KEINOS\KVSQLite3;

/**
 * General-purpose dummy function.
 *
 * @param string $data
 *
 * @return string Returns the same value of the given parameter.
 */
function parrotry(string $data): string
{
    return $data;
}

/**
 * Indent String
 *
 * @param  string $string  Value to be indented.
 * @param  string $indent  Indentation string. (Optional)
 * @return string
 */
function indentString(string $string, string $indent = '  '): string
{
    return str_replace(PHP_EOL, PHP_EOL . $indent, $string);
}
