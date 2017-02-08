<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommandHandler;

class ResourceWorkflowCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflowCreateCommand */
    private $command;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowRepository */
    private $workflowRepository;

    /** @var ResourceWorkflowCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->command = new ResourceWorkflowCreateCommand(['EN' => 'New workflow']);
        $this->workflowRepository = $this->createMock(ResourceWorkflowRepository::class);
        $this->workflowRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->handler = new ResourceWorkflowCreateCommandHandler($this->workflowRepository);
    }

    public function testCreatingResource() {
        $workflow = $this->handler->handle($this->command);
        $this->assertNotNull($workflow);
        $this->assertSame($this->command->getName(), $workflow->getName());
        $this->assertEmpty($workflow->getPlaces());
        $this->assertEmpty($workflow->getTransitions());
        $this->assertNull($workflow->getDiagram());
        $this->assertNull($workflow->getThumbnail());
    }
}
