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
}
