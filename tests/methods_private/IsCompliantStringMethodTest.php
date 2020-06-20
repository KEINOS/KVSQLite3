<?php

declare(strict_types=1);

namespace KEINOS\Tests;

final class IsCompliantStringMethodTest extends TestCase
{
    public function setUp(): void
    {
        parent::setup();

        $this->obj = new \KEINOS\KVSQLite3\KVSQLite3();

        $reflection = new \ReflectionClass($this->obj);
        $this->method = $reflection->getMethod('isCompliantString');

        // Give access permission to "isCompliantString()"
        $this->method->setAccessible(true);
    }

    public function testRegularInput()
    {
        $chars_valid = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.';

        $result = $this->method->invoke($this->obj, $chars_valid);
        $this->assertTrue($result);
    }

    public function testIrregularInputUtf8ButInJapanese()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->method->invoke($this->obj, '無効なキー名');
    }

    public function testIrregularInputUtf8EmojiZwj()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->method->invoke($this->obj, '👨‍👩‍👦‍👦');
    }

    public function testIrregularInputWithUnsupportedChar()
    {
        $chars_valid   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.';
        $chars_invalid = '+';
        $chars = $chars_valid . $chars_invalid;

        $this->expectException(\InvalidArgumentException::class);
        $result = $this->method->invoke($this->obj, $chars);
    }
}
