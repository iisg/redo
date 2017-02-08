<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommandHandler;

class ResourceWorkflowUpdateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    public function testHandling() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->expects($this->once())->method('update')->with(['a'], ['b'], 'diagram', 'thumb');
        $workflowRepository = $this->createMock(ResourceWorkflowRepository::class);
        $workflowRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $command = new ResourceWorkflowUpdateCommand($workflow, ['a'], ['b'], 'diagram', 'thumb');
        $handler = new ResourceWorkflowUpdateCommandHandler($workflowRepository);
        $saved = $handler->handle($command);
        $this->assertSame($workflow, $saved);
    }
}
