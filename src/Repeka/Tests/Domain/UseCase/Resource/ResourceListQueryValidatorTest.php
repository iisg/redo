<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

class ResourceListQueryValidatorTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var ResourceListQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->validator = new ResourceListQueryValidator($this->resourceClassExistsRule);
    }

    public function testPassWhenNoFilters() {
        $command = ResourceListQuery::builder()->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = ResourceListQuery::builder()->filterByResourceClass('books')->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = ResourceListQuery::builder()->filterByResourceClass('invalidResourceClass')->build();
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testValidIfFilterByResourceKind() {
        $command = ResourceListQuery::builder()->filterByResourceKinds([$this->createMock(ResourceKind::class)])->build();
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfFilterByNotResourceKind() {
        $command = ResourceListQuery::builder()->filterByResourceKinds([$this->createMock(ResourceEntity::class)])->build();
        $this->assertFalse($this->validator->isValid($command));
    }
}
