<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Factory\ResourceContentsNormalizer;

class ResourceContentsNormalizerTest extends \PHPUnit_Framework_TestCase {

    /** @var ResourceContentsNormalizer */
    private $normalizer;

    /** @before */
    public function init() {
        $this->normalizer = new ResourceContentsNormalizer();
    }

    /** @dataProvider normalizeExamples */
    public function testNormalize(array $input, array $expectedOutput) {
        $normalized = $this->normalizer->normalize($input);
        $this->assertEquals(
            $expectedOutput,
            $normalized,
            var_export($input, true) . "\nvvvvvvvv\n" . var_export($normalized, true)
        );
    }

    public function normalizeExamples(): array {
        return [
            [[], []],
            [[1 => [['value' => 'abc']]], [1 => [['value' => 'abc']]]],
            [[1 => 'a'], [1 => [['value' => 'a']]]],
            [[1 => ['a']], [1 => [['value' => 'a']]]],
            [['a'], [[['value' => 'a']]]],
            [[2 => ['a', 'b']], [2 => [['value' => 'a'], ['value' => 'b']]]],
            [[1 => ['a']], [1 => [['value' => 'a']]]],
            [[1 => [['value' => 'a', 'submetadata' => [1 => 'a']]]], [1 => [['value' => 'a', 'submetadata' => [1 => [['value' => 'a']]]]]]],
            [[1 => [['submetadata' => [1 => 'a']]]], [1 => [['value' => null, 'submetadata' => [1 => [['value' => 'a']]]]]]],
        ];
    }
}
