<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowDeleteCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowDeleteCommandHandler;

class ResourceWorkflowDeleteCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflowDeleteCommandHandler */
    private $handler;
    /** @var ResourceWorkflowRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $workflowRepository;

    protected function setUp() {
        $this->workflowRepository = $this->createMock(ResourceWorkflowRepository::class);
        $this->handler = new ResourceWorkflowDeleteCommandHandler($this->workflowRepository);
    }

    public function testDeleting() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $command = new ResourceWorkflowDeleteCommand($workflow);
        $this->workflowRepository->expects($this->once())->method('delete')->with($workflow);
        $this->handler->handle($command);
    }
}
