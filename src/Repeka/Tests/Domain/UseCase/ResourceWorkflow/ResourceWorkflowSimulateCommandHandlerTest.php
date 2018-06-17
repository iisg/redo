<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommandHandler;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;
use Repeka\Tests\Domain\Factory\SampleResourceWorkflowDriverFactory;

class ResourceWorkflowSimulateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowDriver */
    private $workflowDriver;
    /** @var ResourceWorkflowSimulateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->workflowDriver = $this->createMock(ResourceWorkflowDriver::class);
        $this->workflowDriver->expects($this->any())->method('getPlaces')->willReturn(['place']);
        $this->workflowDriver->expects($this->any())->method('getTransitions')->willReturn(['transition']);
        $this->handler = new ResourceWorkflowSimulateCommandHandler(new SampleResourceWorkflowDriverFactory($this->workflowDriver));
    }

    public function testHandlingSimpleWorkflow() {
        $command = new ResourceWorkflowSimulateCommand(
            [['id' => 'place', 'label' => []]],
            [new ResourceWorkflowTransition([], [], [], 'transition')]
        );
        $this->workflowDriver->expects($this->never())->method('apply');
        $this->workflowDriver->expects($this->once())->method('setCurrentPlaces')->with($this->isInstanceOf(ResourceEntity::class), []);
        $result = $this->handler->handle($command);
        $this->assertCount(2, $result);
        $this->assertCount(1, $result['places']);
        $this->assertCount(1, $result['transitions']);
    }

    public function testMovingToDesiredCurrentPlace() {
        $command = new ResourceWorkflowSimulateCommand([], [], ['current']);
        $this->workflowDriver->expects($this->never())->method('apply');
        $this->workflowDriver->expects($this->once())->method('setCurrentPlaces')
            ->with($this->isInstanceOf(ResourceEntity::class), ['current']);
        $this->handler->handle($command);
    }

    public function testApplyingTransition() {
        $command = new ResourceWorkflowSimulateCommand([], [], [], 'transition');
        $this->workflowDriver->expects($this->once())->method('apply')->with($this->isInstanceOf(ResourceEntity::class), 'transition');
        $this->handler->handle($command);
    }
}
