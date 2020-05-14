<?php
declare(strict_types=1);

namespace KEINOS\KVSQLite3;

final class Hello
{
    public function to(string $name): string
    {
        return sayHelloTo($name);
    }
}
