<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\Validation\MetadataConstraints\UniqueInResourceClassConstraint;
use Repeka\Tests\Traits\StubsTrait;

class UniqueInResourceClassConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private $repeatedValue = 'repeatedValue';
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var UniqueInResourceClassConstraint */
    private $constraint;
    /** @var Metadata */
    private $uniqueMetadata;
    /** @var Metadata */
    private $repeatedMetadata;
    /** @var ResourceEntity */
    private $testResource;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $editedResource;

    public function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->constraint = new UniqueInResourceClassConstraint($this->resourceRepository);
        $this->uniqueMetadata = $this->createMetadataMock(1, null, MetadataControl::TEXT(), ['uniqueInResourceClass' => true]);
        $this->repeatedMetadata = $this->createMetadataMock(1, null, MetadataControl::TEXT(), ['uniqueInResourceClass' => false]);
        $this->testResource = $this->createResourceMock(
            1,
            $this->createResourceKindMock(),
            [$this->uniqueMetadata->getId() => $this->repeatedValue]
        );
        $this->resourceRepository->method('findByQuery')->willReturn(new PageResult([$this->testResource], 2));
        $this->editedResource = $this->createResourceMock(2);
    }

    public function testConstraintDoesNotAcceptIncorrectArguments() {
        $this->assertFalse($this->constraint->isConfigValid(null));
        $this->assertFalse($this->constraint->isConfigValid(0));
        $this->assertFalse($this->constraint->isConfigValid('false'));
        $this->assertTrue($this->constraint->isConfigValid(false));
    }

    public function testValidateIsUniqueRejectsRepeatedMetadataValue() {
        $this->expectException(DomainException::class);
        $this->constraint->validateIsUnique(
            $this->uniqueMetadata->getId(),
            $this->repeatedValue,
            $this->uniqueMetadata->getResourceClass(),
            null
        );
    }

    public function testSkipsValidationIfNoValue() {
        $this->constraint->validateSingle(
            $this->uniqueMetadata,
            null,
            $this->createResourceMock(2)
        );
    }

    public function testUniquenessNotDemandedWhenConstraintValueIsFalse() {
        $this->constraint->validateSingle($this->repeatedMetadata, $this->repeatedValue, $this->editedResource);
    }

    public function testUniquenessDemandedWhenConstraintValueIsTrue() {
        $this->expectException(DomainException::class);
        $this->constraint->validateSingle($this->uniqueMetadata, $this->repeatedValue, $this->editedResource);
    }

    public function testSameResourceNotTreatedAsRepetition() {
        $this->constraint->validateSingle(
            $this->uniqueMetadata,
            $this->repeatedValue,
            $this->testResource
        );
    }

    public function testFailingBecauseOfDuplicatedValuesInCurrentMetadata() {
        $this->expectException(DomainException::class);
        $this->constraint->validateAll($this->uniqueMetadata, ['a', 'a'], $this->editedResource);
    }
}
