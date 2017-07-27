<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

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

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new ResourceListQuery('books');
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testPassWithSystemResourceKindsAndWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new ResourceListQuery('books', true);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = new ResourceListQuery('resourceClass');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithSystemResourceKindsAndWhenInvalidResourceClass() {
        $command = new ResourceListQuery('resourceClass', true);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenBlankResourceClass() {
        $command = new ResourceListQuery('', true);
        $this->assertFalse($this->validator->isValid($command));
    }
}
