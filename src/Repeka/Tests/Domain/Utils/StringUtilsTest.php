<?php
namespace Repeka\Tests\Domain\Utils;

use Repeka\Domain\Utils\StringUtils;

class StringUtilsTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider joinPathsExamples */
    public function testJoinPaths(array $paths, $expected) {
        $this->assertEquals($expected, StringUtils::joinPaths(...$paths));
    }

    public function joinPathsExamples() {
        return [
            [['', ''], ''],
            [['', '/'], '/'],
            [['/', 'a'], '/a'],
            [['/', '/a'], '/a'],
            [['abc', 'def'], 'abc/def'],
            [['abc', '/def'], 'abc/def'],
            [['/abc', 'def'], '/abc/def'],
            [['', 'foo.jpg'], 'foo.jpg'],
            [['dir', '0', 'foo.jpg'], 'dir/0/foo.jpg'],
        ];
    }

    /** @dataProvider normalizeEntityNameExamples */
    public function testNormalizingName(string $name, $expected) {
        $this->assertEquals($expected, StringUtils::normalizeEntityName($name));
    }

    public function normalizeEntityNameExamples() {
        return [
            ['opis', 'opis',],
            ['   opis ', 'opis',],
            ['Opis', 'opis',],
            ['opis szerszy', 'opis_szerszy',],
            ['opisSzerszy', 'opis_szerszy',],
            ['opis-szerszy', 'opis_szerszy',],
            ['opis.szerszy', 'opis_szerszy',],
            ['opis Dłuższy', 'opis_dluzszy',],
            ['ŻÓŁW PChłę 2& * popchnął%3', 'zolw_pchle_2_popchnal_3',],
            ['emo👍🏊🏽‍♀️ji]', 'emo_ji'],
            ['100', '100']
        ];
    }
}
