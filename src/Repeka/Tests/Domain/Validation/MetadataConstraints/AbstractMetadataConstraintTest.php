<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class AbstractMetadataConstraintTest extends \PHPUnit_Framework_TestCase {
    public function testGeneratesNames() {
        $generatedName = (new TestDummyConstraint())->getConstraintName();
        $this->assertEquals('testDummy', $generatedName);
    }
}

// @codingStandardsIgnoreStart
class TestDummyConstraint extends AbstractMetadataConstraint {
    public function getSupportedControls(): array {
        throw new \RuntimeException('Unexpected method call');
    }

    public function isConfigValid($config): bool {
        throw new \RuntimeException('Unexpected method call');
    }

    public function validateSingle(Metadata $metadata, $config, $metadataValue) {
        throw new \RuntimeException('Unexpected method call');
    }
}
