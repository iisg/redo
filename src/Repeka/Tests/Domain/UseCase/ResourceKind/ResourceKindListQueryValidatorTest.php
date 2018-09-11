<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceMetadataSortCorrectStructureRule;

class ResourceKindListQueryValidatorTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceMetadataSortCorrectStructureRule;
    /** @var ResourceKindListQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->resourceMetadataSortCorrectStructureRule = $this->createMock(ResourceMetadataSortCorrectStructureRule::class);
        $this->validator = new ResourceKindListQueryValidator(
            $this->resourceClassExistsRule,
            $this->resourceMetadataSortCorrectStructureRule
        );
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $this->resourceMetadataSortCorrectStructureRule->method('validate')->willReturn(true);
        $command = ResourceKindListQuery::builder()->filterByResourceClass('books')->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = ResourceKindListQuery::builder()->filterByResourceClass('resourceClass')->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenBlankResourceClass() {
        $command = ResourceKindListQuery::builder()->filterByResourceClass('')->build();
        $this->assertFalse($this->validator->isValid($command));
    }
}
