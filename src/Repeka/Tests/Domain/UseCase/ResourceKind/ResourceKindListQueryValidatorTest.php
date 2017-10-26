<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

class ResourceKindListQueryValidatorTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var ResourceKindListQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->validator = new ResourceKindListQueryValidator($this->resourceClassExistsRule);
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new ResourceKindListQuery('books');
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testPassWithSystemResourceKindsAndWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new ResourceKindListQuery('books', true);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = new ResourceKindListQuery('resourceClass');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithSystemResourceKindsAndWhenInvalidResourceClass() {
        $command = new ResourceKindListQuery('resourceClass', true);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenBlankResourceClass() {
        $command = new ResourceKindListQuery('', true);
        $this->assertFalse($this->validator->isValid($command));
    }
}