<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Repeka\Domain\Service\RegexNormalizer;

class RegexImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var RegexImportTransformTest */
    private $regexTransform;

    protected function setUp() {
        $regexNormalizer = $this->createMock(RegexNormalizer::class);
        $this->regexTransform = new RegexReplaceImportTransform($regexNormalizer);
        $regexNormalizer->method('normalize')->willReturnArgument(0);
    }

    public function testTransforming() {
        $this->assertEquals(['a'], $this->regexTransform->apply(['b'], ['regex' => '/b/', 'replacement' => 'a']));
        $this->assertEquals(['oko'], $this->regexTransform->apply(['babka'], ['regex' => '/[abc]+/', 'replacement' => 'o']));
    }
}
