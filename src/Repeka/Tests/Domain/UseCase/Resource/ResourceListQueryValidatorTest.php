<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Repeka\Domain\Validation\Rules\ResourceMetadataSortCorrectStructureRule;

class ResourceListQueryValidatorTest extends \PHPUnit_Framework_TestCase {
    private $resourceContentsStructureRule;
    private $resourceMetadataSortCorrectStructureRule;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var ResourceListQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->resourceContentsStructureRule = $this->createMock(ResourceContentsCorrectStructureRule::class);
        $this->resourceMetadataSortCorrectStructureRule = $this->createMock(ResourceMetadataSortCorrectStructureRule::class);
        $this->validator = new ResourceListQueryValidator(
            $this->resourceClassExistsRule,
            $this->resourceContentsStructureRule,
            $this->resourceMetadataSortCorrectStructureRule
        );
    }

    public function testPassWhenNoFilters() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $this->resourceMetadataSortCorrectStructureRule->method('validate')->willReturn(true);
        $command = ResourceListQuery::builder()->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $this->resourceMetadataSortCorrectStructureRule->method('validate')->willReturn(true);
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceListQuery::builder()->filterByResourceClass('books')->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceListQuery::builder()->filterByResourceClass('invalidResourceClass')->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testValidIfFilterByResourceKind() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $this->resourceMetadataSortCorrectStructureRule->method('validate')->willReturn(true);
        $command = ResourceListQuery::builder()->filterByResourceKinds([$this->createMock(ResourceKind::class)])->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfFilterByNotResourceKind() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceListQuery::builder()->filterByResourceKinds([$this->createMock(ResourceEntity::class)])->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testPassWhenPageAndResultsPerPageArePositiveValue() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $this->resourceMetadataSortCorrectStructureRule->method('validate')->willReturn(true);
        $command = ResourceListQuery::builder()->setPage(1)->setResultsPerPage(5)->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfWrongContents() {
        $command = ResourceListQuery::builder()->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testPassWhenSortByCorrectStructure() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $this->resourceMetadataSortCorrectStructureRule->method('validate')->willReturn(true);
        $command = ResourceListQuery::builder()->sortByMetadataIds([['metadataId' => 1, 'direction' => 'ASC']])->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenSortByInvalidStructure() {
        $command = ResourceListQuery::builder()->sortByMetadataIds([['badKey' => 'badKey']])->build();
        $this->assertFalse($this->validator->isValid($command));
    }
}
