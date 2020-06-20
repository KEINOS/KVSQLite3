<?php

declare(strict_types=1);

namespace KEINOS\Tests;

/**
 * Need to implement
 */
final class SpecialCharsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->sqlite3 = new \KEINOS\KVSQLite3\KVSQLite3();
    }

    public function testMeaningfulCharsInSystem()
    {
        $result_expect = ',\'" ./\\=?!:;","a","b"';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        print_r(get_class_methods('SpecialCharsTest'));

        $this->assertSame($result_expect, $result_actual);
    }

    public function testHalfKatakana()
    {
        $result_expect = 'ｧｰｭｿﾏﾞﾟ';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testFullKatakana()
    {
        $result_expect = 'ヲンヰヱヴーヾ・';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testMachineDependentCharacters()
    {
        $result_expect = '㌶Ⅲ⑳㏾☎㈱髙﨑';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testEucUtf8MappingChars()
    {
        $result_expect = '¢£¬‖−〜―';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testSurrogatePairs()
    {
        $result_expect = '𠀋𡈽𡌛𡑮𡢽𠮟𡚴𡸴𣇄𣗄';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testEmojis()
    {
        $result_expect = '😀🐱🚗';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testCharsThatGarblesInEuc()
    {
        $result_expect = 'ソ能表';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testJavascript()
    {
        $result_expect = '<script>alert(\'Bug!!!\');</script>';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testHtml()
    {
        $result_expect = '<input type="text" value="';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testSpecialHtmlChars()
    {
        $result_expect = '&lt;&copy;&amp;';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }

    public function testDbInjection()
    {
        $result_expect = '\';delete from user_table;';
        $result_actual = $this->sqlite3->parrotry($result_expect);

        $this->assertSame($result_expect, $result_actual);
    }
}
