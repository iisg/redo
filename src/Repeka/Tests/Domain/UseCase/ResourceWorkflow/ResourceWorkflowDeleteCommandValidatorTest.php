<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowDeleteCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowDeleteCommandValidator;

class ResourceWorkflowDeleteCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;
    /** @var ResourceWorkflowDeleteCommandValidator */
    private $validator;

    protected function setUp() {
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->validator = new ResourceWorkflowDeleteCommandValidator($this->resourceKindRepository);
    }

    public function testPositive() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $this->resourceKindRepository->method('countByResourceWorkflow')->with($workflow)->willReturn(0);
        $command = new ResourceWorkflowDeleteCommand($workflow);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testNegative() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $this->resourceKindRepository->method('countByResourceWorkflow')->with($workflow)->willReturn(1);
        $command = new ResourceWorkflowDeleteCommand($workflow);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testExceptionMessageTellsWhatIsWrong() {
        $this->expectExceptionMessage('workflow is assigned to one of resource kinds');
        $this->resourceKindRepository->method('countByResourceWorkflow')->willReturn(1);
        $this->validator->validate(new ResourceWorkflowDeleteCommand($this->createMock(ResourceWorkflow::class)));
    }
}
