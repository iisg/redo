<?php
namespace Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Validation\MetadataConstraints\RelatedResourceKindConstraint;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Tests\Traits\StubsTrait;

class RelatedResourceKindConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKind|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var RelatedResourceKindConstraint */
    private $constraint;

    protected function setUp() {
        $this->resourceKind = $this->createResourceKindMock(123);
        $this->resource = $this->createResourceMock(1, $this->resourceKind);
        $repository = $this->createRepositoryStub(ResourceRepository::class, [$this->resource]);
        $this->constraint = new RelatedResourceKindConstraint($repository, $this->createMock(EntityExistsRule::class));
    }

    public function testAcceptsWhenNoResourceKindIdsAndNoValueAreProvided() {
        $this->constraint->validateAll($this->createMetadataMock(), [], $this->resource);
    }

    public function testRejectsWhenValueAndNoResourceKindIdsAreProvided() {
        $this->expectException(InvalidCommandException::class);
        $this->constraint->validateAll($this->createMetadataMock(), [1], $this->resource);
    }

    public function testAcceptsValueWhenSoleResourceKindIdMatches() {
        $this->constraint->validateAll($this->createMetadataMockWithResourceKindConstraint([123]), [1], $this->resource);
    }

    public function testAcceptsValueWhenAnyResourceKindIdMatches() {
        $this->resourceKind->method('getId')->willReturn(123);
        $this->constraint->validateAll($this->createMetadataMockWithResourceKindConstraint([100, 111, 123, 200]), [1], $this->resource);
    }

    public function testRejectsValueWhenResourceKindIdDoesNotMatch() {
        $this->expectException(InvalidCommandException::class);
        $this->resourceKind->method('getId')->willReturn(123);
        $this->constraint->validateAll($this->createMetadataMockWithResourceKindConstraint([100]), [1], $this->resource);
    }

    public function testAcceptsValueWhenResourceDoesNotExist() {
        $repository = $this->createMock(ResourceRepository::class);
        $repository->method('findOne')->willThrowException(new EntityNotFoundException('dummy', 0));
        $constraint = new RelatedResourceKindConstraint($repository, $this->createMock(EntityExistsRule::class));
        $this->resourceKind->expects($this->never())->method('getId');
        $constraint->validateAll($this->createMetadataMockWithResourceKindConstraint([100]), [2], $this->resource);
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

    private function createMetadataMockWithResourceKindConstraint(array $allowedResourceKindIds) {
        return $this->createMetadataMock(1, null, null, ['resourceKind' => $allowedResourceKindIds]);
    }
}
