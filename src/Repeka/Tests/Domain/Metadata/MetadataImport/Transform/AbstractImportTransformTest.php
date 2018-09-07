<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Repeka\Domain\Service\RegexNormalizer;

class AbstractImportTransformTest extends \PHPUnit_Framework_TestCase {
    public function testGeneratingName() {
        $this->assertEquals('join', (new JoinImportTransform())->getName());
        $this->assertEquals('regexReplace', (new RegexReplaceImportTransform($this->createMock(RegexNormalizer::class)))->getName());
    }
}
