<?php
namespace Repeka\Tests\Application\Factory;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\Workflow\SymfonyResourceWorkflowDriver;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Exception\ResourceWorkflow\CannotApplyTransitionException;

class SymfonyResourceWorkflowDriverTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceWorkflow */
    private $resourceWorkflow;
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceEntity */
    private $resource;
    /** @var SymfonyResourceWorkflowDriver */
    private $workflowDriver;

    protected function setUp() {
        $this->resourceWorkflow = $this->createMock(ResourceWorkflow::class);
        $this->resourceWorkflow->method('getPlaces')->willReturn([
            $this->placeMock('A'),
            $this->placeMock('B'),
            $this->placeMock('C')
        ]);
        $this->resourceWorkflow->method('getTransitions')->willReturn([
            $this->transitionMock('AB'),
            $this->transitionMock('BC'),
            $this->transitionMock('AC'),
        ]);
        $this->resource = new ResourceEntity($this->createMock(ResourceKind::class), [], 'books');
        $this->workflowDriver = new SymfonyResourceWorkflowDriver($this->resourceWorkflow);
    }

    private function placeMock(string $placeId) {
        $place = $this->createMock(ResourceWorkflowPlace::class);
        $place->method('getId')->willReturn($placeId);
        return $place;
    }

    private function transitionMock(string $transitionId) {
        $transition = $this->createMock(ResourceWorkflowTransition::class);
        $transition->method('getId')->willReturn($transitionId);
        $transition->method('getFromIds')->willReturn([$transitionId{0}]);
        $transition->method('getToIds')->willReturn([$transitionId{1}]);
        return $transition;
    }

    public function testGetPlacesForResource() {
        $this->assertEquals(['A'], $this->workflowDriver->getPlaces($this->resource));
    }

    public function testGetTransitionsForResource() {
        $this->assertEquals(['AB', 'AC'], $this->workflowDriver->getTransitions($this->resource));
    }

    public function testSettingCurrentPlaces() {
        $this->workflowDriver->setCurrentPlaces($this->resource, ['B']);
        $this->assertEquals(['B'], $this->workflowDriver->getPlaces($this->resource));
    }

    public function testApplyingTransition() {
        $this->workflowDriver->apply($this->resource, 'AB');
        $this->assertEquals(['B'], $this->workflowDriver->getPlaces($this->resource));
    }

    public function testApplyingTransitionWithInvalidId() {
        $this->expectException(CannotApplyTransitionException::class);
        $this->workflowDriver->apply($this->resource, 'ABC');
    }

    public function testApplyingTransitionInvalidForState() {
        $this->expectException(CannotApplyTransitionException::class);
        $this->workflowDriver->apply($this->resource, 'BC');
    }
}
