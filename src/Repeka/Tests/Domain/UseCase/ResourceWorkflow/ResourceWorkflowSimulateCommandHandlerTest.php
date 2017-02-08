<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Factory\ResourceWorkflowStrategy;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommandHandler;
use Repeka\Tests\Domain\Factory\SampleResourceWorkflowStrategyFactory;

class ResourceWorkflowSimulateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowStrategy */
    private $workflowStrategy;
    /** @var ResourceWorkflowSimulateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->workflowStrategy = $this->createMock(ResourceWorkflowStrategy::class);
        $this->workflowStrategy->expects($this->any())->method('getPlaces')->willReturn(['place']);
        $this->workflowStrategy->expects($this->any())->method('getTransitions')->willReturn(['transition']);
        $this->handler = new ResourceWorkflowSimulateCommandHandler(new SampleResourceWorkflowStrategyFactory($this->workflowStrategy));
    }

    public function testHandlingSimpleWorkflow() {
        $command = new ResourceWorkflowSimulateCommand(
            [['id' => 'place', 'label' => []]],
            [new ResourceWorkflowTransition([], [], [], 'transition')]
        );
        $this->workflowStrategy->expects($this->never())->method('apply');
        $this->workflowStrategy->expects($this->once())->method('move')->with($this->isInstanceOf(ResourceEntity::class), []);
        $result = $this->handler->handle($command);
        $this->assertCount(2, $result);
        $this->assertCount(1, $result['places']);
        $this->assertCount(1, $result['transitions']);
    }

    public function testMovingToDesiredCurrentPlace() {
        $command = new ResourceWorkflowSimulateCommand([], [], ['current']);
        $this->workflowStrategy->expects($this->never())->method('apply');
        $this->workflowStrategy->expects($this->once())->method('move')->with($this->isInstanceOf(ResourceEntity::class), ['current']);
        $this->handler->handle($command);
    }

    public function testApplyingTransition() {
        $command = new ResourceWorkflowSimulateCommand([], [], [], 'transition');
        $this->workflowStrategy->expects($this->once())->method('apply')->with($this->isInstanceOf(ResourceEntity::class), 'transition');
        $this->handler->handle($command);
    }
}
