<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Metadata\MetadataConstraintCheckQuery;
use Repeka\Domain\UseCase\Metadata\MetadataConstraintCheckQueryHandler;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Tests\Traits\StubsTrait;

class MetadataConstraintCheckQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataConstraintCheckQueryHandler */
    private $handler;
    /** @var MetadataConstraintCheckQuery */
    private $query;
    /** @var AbstractMetadataConstraint|\PHPUnit_Framework_MockObject_MockObject */
    private $constraint;

    protected function setUp() {
        $this->constraint = $this->createMock(AbstractMetadataConstraint::class);
        $metadata = $this->createMetadataMock();
        $resource = $this->createResourceMock(1, null, [1 => 'A']);
        $newContents = ResourceContents::fromArray([1 => 'B']);
        $this->query = new MetadataConstraintCheckQuery($this->constraint, 'X', $metadata, $newContents, $resource);
        $this->handler = new MetadataConstraintCheckQueryHandler();
    }

    public function testThrowsExceptionWhenConstraintNotOk() {
        $this->expectException(\InvalidArgumentException::class);
        $this->constraint->method('validateSingle')->willThrowException(new \InvalidArgumentException());
        $this->handler->handle($this->query);
    }

    public function testMaintainsContentsWhenConstraintOk() {
        /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject $resourceEntity */
        $resourceEntity = $this->query->getResource();
        $resourceEntity->expects($this->exactly(2))->method('updateContents')
            ->withConsecutive([ResourceContents::fromArray([1 => 'B'])], [$resourceEntity->getContents()]);
        $this->handler->handle($this->query);
    }

    public function testMaintainsContentsWhenConstraintNotOk() {
        $this->expectException(\InvalidArgumentException::class);
        /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject $resourceEntity */
        $resourceEntity = $this->query->getResource();
        $this->constraint->method('validateSingle')->willThrowException(new \InvalidArgumentException());
        $resourceEntity->expects($this->exactly(2))->method('updateContents')
            ->withConsecutive([ResourceContents::fromArray([1 => 'B'])], [$resourceEntity->getContents()]);
        $this->handler->handle($this->query);
    }

    public function testValidatingWithoutResourceEntity() {
        $this->expectException(\InvalidArgumentException::class);
        $this->query = new MetadataConstraintCheckQuery(
            $this->constraint,
            'X',
            $this->createMetadataMock(),
            ResourceContents::fromArray([1 => 'B']),
            $this->createResourceKindMock()
        );
        $this->constraint->method('validateSingle')->willThrowException(new \InvalidArgumentException());
        $this->handler->handle($this->query);
    }
}
