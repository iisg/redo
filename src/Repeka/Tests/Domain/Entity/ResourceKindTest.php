<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\IllegalEntityStateException;

class ResourceKindTest extends \PHPUnit_Framework_TestCase {

    public function testAddingMetadata() {
        $metadata = $this->createMock(Metadata::class);
        $resourceKind = new ResourceKind([]);
        $resourceKind->addMetadata($metadata);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertContains($metadata, $resourceKind->getMetadataList());
    }

    public function testAddingTheSameMetadataTwice() {
        $this->expectException(IllegalEntityStateException::class);
        $metadata = $this->createMock(Metadata::class);
        $resourceKind = new ResourceKind([]);
        $resourceKind->addMetadata($metadata);
        $resourceKind->addMetadata($metadata);
    }
}
