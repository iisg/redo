<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQueryValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

class ResourceWorkflowListQueryValidatorTest extends \PHPUnit_Framework_TestCase {

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $resourceClassExistsRule;
    /** @var ResourceWorkflowListQueryValidator */
    private $validator;

    protected function setUp() {
        $this->resourceClassExistsRule = $this->createMock(ResourceClassExistsRule::class);
        $this->validator = new ResourceWorkflowListQueryValidator($this->resourceClassExistsRule);
    }

    public function testPassWhenResourceClassExists() {
        $this->resourceClassExistsRule->method('validate')->with('books')->willReturn(true);
        $command = new ResourceWorkflowListQuery('books');
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidWhenInvalidResourceClass() {
        $command = new ResourceWorkflowListQuery('resourceClass');
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testValidWhenBlankResourceClass() {
        $command = new ResourceWorkflowListQuery('');
        $this->assertTrue($this->validator->isValid($command));
    }
}
