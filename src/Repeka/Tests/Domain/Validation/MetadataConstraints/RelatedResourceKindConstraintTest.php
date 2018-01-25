<?php
namespace Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Validation\MetadataConstraints\RelatedResourceKindConstraint;
use Repeka\Domain\Validation\Rules\EntityExistsRule;

class RelatedResourceKindConstraintTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceKind|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var RelatedResourceKindConstraint */
    private $constraint;

    protected function setUp() {
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getKind')->willReturn($this->resourceKind);
        $repository = $this->createMock(ResourceRepository::class);
        $repository->method('findOne')->willReturn($resource);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->method('getId')->willReturn(0);
        $this->constraint = new RelatedResourceKindConstraint($repository, $this->createMock(EntityExistsRule::class));
    }

    public function testAcceptsWhenNoResourceKindIdsAndNoValueAreProvided() {
        $this->resourceKind->expects($this->never())->method('getId');
        $this->constraint->validateAll([], []);
    }

    public function testRejectsWhenValueAndNoResourceKindIdsAreProvided() {
        $this->expectException(InvalidCommandException::class);
        $this->constraint->validateAll([], [['value' => $this->resource]]);
    }

    public function testAcceptsValueWhenSoleResourceKindIdMatches() {
        $this->resourceKind->expects($this->once())->method('getId')->willReturn(123);
        $this->constraint->validateAll([123], [['value' => $this->resource]]);
    }

    public function testAcceptsValueWhenAnyResourceKindIdMatches() {
        $this->resourceKind->expects($this->once())->method('getId')->willReturn(123);
        $this->constraint->validateAll([100, 111, 123, 200], [['value' => $this->resource]]);
    }

    public function testRejectsValueWhenResourceKindIdDoesNotMatch() {
        $this->expectException(InvalidCommandException::class);
        $this->resourceKind->expects($this->once())->method('getId')->willReturn(123);
        $this->constraint->validateAll([100], [['value' => $this->resource]]);
    }

    public function testRejectsValueWhenResourceDoesNotExist() {
        $this->expectException(EntityNotFoundException::class);
        $repository = $this->createMock(ResourceRepository::class);
        $repository->method('findOne')->willThrowException(new EntityNotFoundException('dummy', 0));
        $constraint = new RelatedResourceKindConstraint($repository, $this->createMock(EntityExistsRule::class));
        $this->resourceKind->expects($this->never())->method('getId');
        $constraint->validateAll([100], [['value' => $this->resource]]);
    }

    public function testAcceptsArgumentWhenResourceKindsExist() {
        $entityExists = $this->createMock(EntityExistsRule::class);
        $entityExists->expects($this->exactly(3))->method('validate')->willReturn(true);
        $entityExists->method('forEntityType')->willReturnSelf();
        $constraint = new RelatedResourceKindConstraint($this->createMock(ResourceRepository::class), $entityExists);
        $this->assertTrue($constraint->isConfigValid([0, 1, 2]));
    }

    public function testAcceptsArgumentWhenResourceKindsEmpty() {
        $entityExists = $this->createMock(EntityExistsRule::class);
        $entityExists->expects($this->never())->method('validate');
        $entityExists->method('forEntityType')->willReturnSelf();
        $constraint = new RelatedResourceKindConstraint($this->createMock(ResourceRepository::class), $entityExists);
        $this->assertTrue($constraint->isConfigValid([]));
    }

    public function testRejectsArgumentWhenResourceKindDoesNotExist() {
        $entityExists = $this->createMock(EntityExistsRule::class);
        $entityExists->expects($this->exactly(3))->method('validate')->willReturn(true, true, false);
        $entityExists->method('forEntityType')->willReturnSelf();
        $constraint = new RelatedResourceKindConstraint($this->createMock(ResourceRepository::class), $entityExists);
        $this->assertFalse($constraint->isConfigValid([0, 1, 2]));
    }
}
