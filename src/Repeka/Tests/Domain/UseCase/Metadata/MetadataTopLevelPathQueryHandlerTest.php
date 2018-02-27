<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\UseCase\Metadata\MetadataTopLevelPathQuery;
use Repeka\Domain\UseCase\Metadata\MetadataTopLevelPathQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class MetadataTopLevelPathQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataTopLevelPathQueryHandler */
    private $handler;

    protected function setUp() {
        $this->handler = new MetadataTopLevelPathQueryHandler();
    }

    public function testParentlessMetadata() {
        $metadata = $this->createMetadataMock(1);
        $metadata->method('isTopLevel')->willReturn(true);
        $path = $this->handler->handle(new MetadataTopLevelPathQuery($metadata));
        $this->assertEmpty($path);
    }

    public function testMetadataWithOneParent() {
        $metadata = $this->createMetadataMock(1);
        $parentMetadata = $this->createMetadataMock(2);
        $metadata->method('getParent')->willReturn($parentMetadata);
        $parentMetadata->method('isTopLevel')->willReturn(true);
        $path = $this->handler->handle(new MetadataTopLevelPathQuery($metadata));
        $this->assertEquals([$parentMetadata], $path);
    }

    public function testMetadataWithTwoParentsInHierarchy() {
        $metadata = $this->createMetadataMock(1);
        $parentMetadata = $this->createMetadataMock(2);
        $parentParentMetadata = $this->createMetadataMock(2);
        $metadata->method('getParent')->willReturn($parentMetadata);
        $parentMetadata->method('getParent')->willReturn($parentParentMetadata);
        $parentParentMetadata->method('isTopLevel')->willReturn(true);
        $path = $this->handler->handle(new MetadataTopLevelPathQuery($metadata));
        $this->assertEquals([$parentMetadata, $parentParentMetadata], $path);
    }
}
