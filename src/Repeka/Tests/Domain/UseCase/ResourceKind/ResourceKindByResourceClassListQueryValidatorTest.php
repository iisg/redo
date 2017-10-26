<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindByResourceClassListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindByResourceClassListQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

class ResourceKindByResourceClassListQueryValidatorTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var ResourceKindByResourceClassListQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->validator = new ResourceKindByResourceClassListQueryValidator($this->resourceClassExistsRule);
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new ResourceKindByResourceClassListQuery('books');
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testPassWithSystemResourceKindsAndWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new ResourceKindByResourceClassListQuery('books');
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = new ResourceKindByResourceClassListQuery('resourceClass');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithSystemResourceKindsAndWhenInvalidResourceClass() {
        $command = new ResourceKindByResourceClassListQuery('resourceClass');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenBlankResourceClass() {
        $command = new ResourceKindByResourceClassListQuery('');
        $this->assertFalse($this->validator->isValid($command));
    }
}
