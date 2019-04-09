<?php
namespace Repeka\Tests\Application\Service;

use Repeka\Application\Service\PhpRegexNormalizer;

class PhpRegexNormalizerTest extends \PHPUnit_Framework_TestCase {
    private function normalizeAndMatch(string $regex, array $positive, array $negative = []) {
        $normalizer = new PhpRegexNormalizer();
        $normalizedRegex = $normalizer->normalize($regex);
        foreach ($positive as $str) {
            $this->assertEquals(1, preg_match($normalizedRegex, $str));
        }
        foreach ($negative as $str) {
            $this->assertEquals(0, preg_match($normalizedRegex, $str));
        }
    }

    public function testNormalizesGenericRegex() {
        $this->normalizeAndMatch(
            '^a.c$',
            ['abc', 'aBc', 'a.c', 'a/c', 'a\\c'],
            ['aabc', '_abc', 'abc_', 'ac', '/abc', '\\abc', 'abc/', 'abc\\', '/abc/', '\\abc\\']
        );
    }

    public function testNormalizesForwardSlash() {
        $this->normalizeAndMatch(
            '^x/x',
            ['x/x', 'x/x...'],
            ['x\\x', 'x.x', 'xxx', 'xax']
        );
        $this->normalizeAndMatch(
            '/x',
            ['/x', '.../x', '/x...', 'x/x', '/x/'],
            ['\\x', '.x']
        );
        $this->normalizeAndMatch(
            'x/',
            ['x/', '...x/', 'x/...', 'x/x', '/x/'],
            ['x\\', 'x.']
        );
    }
}
