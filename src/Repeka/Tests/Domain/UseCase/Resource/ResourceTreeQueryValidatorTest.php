<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;
use Repeka\Domain\UseCase\Resource\ResourceTreeQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;

class ResourceTreeQueryValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceClassExistsRule|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var ResourceContentsCorrectStructureRule|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceContentsStructureRule;
    /** @var ResourceTreeQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->resourceContentsStructureRule = $this->createMock(ResourceContentsCorrectStructureRule::class);
        $this->validator = new ResourceTreeQueryValidator(
            $this->resourceClassExistsRule,
            $this->resourceContentsStructureRule
        );
    }

    public function testPassWhenNoFilters() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = ResourceTreeQuery::builder()->filterByResourceClass('books')->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->filterByResourceClass('invalidResourceClass')->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testPassIfFilterByResourceKind() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->filterByResourceKinds([$this->createMock(ResourceKind::class)])->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfFilterByNotResourceKind() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->filterByResourceKinds([$this->createMock(ResourceEntity::class)])->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testPassWhenPageAndResultsPerPageArePositiveValue() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->setPage(1)->setResultsPerPage(5)->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfWrongContents() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(false);
        $command = ResourceTreeQuery::builder()->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenNegativeDepth() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->includeWithinDepth(-1)->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testPassWhenPositiveOrZeroDepth() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->includeWithinDepth(1)->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenNegativeSiblings() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->includeWithinDepth(-1)->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testPassWhenPositiveSiblings() {
        $this->resourceContentsStructureRule->method('validate')->willReturn(true);
        $command = ResourceTreeQuery::builder()->includeWithinDepth(1)->build();
        $this->assertTrue($this->validator->isValid($command));
    }
}
