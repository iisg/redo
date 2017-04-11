<?php
namespace Repeka\Tests\Application\Factory;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\Factory\ResourceSymfonyWorkflowStrategy;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Exception\ResourceWorkflow\CannotApplyTransitionException;

class ResourceSymfonyWorkflowStrategyTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceWorkflow */
    private $resourceWorkflow;
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceEntity */
    private $resource;
    /** @var ResourceSymfonyWorkflowStrategy */
    private $workflowStrategy;

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
        $this->resource = new ResourceEntity($this->createMock(ResourceKind::class), []);
        $this->workflowStrategy = new ResourceSymfonyWorkflowStrategy($this->resourceWorkflow);
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
        $this->assertEquals(['A'], $this->workflowStrategy->getPlaces($this->resource));
    }

    public function testGetTransitionsForResource() {
        $this->assertEquals(['AB', 'AC'], $this->workflowStrategy->getTransitions($this->resource));
    }

    public function testSettingCurrentPlaces() {
        $this->workflowStrategy->setCurrentPlaces($this->resource, ['B']);
        $this->assertEquals(['B'], $this->workflowStrategy->getPlaces($this->resource));
    }

    public function testApplyingTransition() {
        $this->workflowStrategy->apply($this->resource, 'AB');
        $this->assertEquals(['B'], $this->workflowStrategy->getPlaces($this->resource));
    }

    public function testApplyingTransitionWithInvalidId() {
        $this->expectException(CannotApplyTransitionException::class);
        $this->workflowStrategy->apply($this->resource, 'ABC');
    }

    public function testApplyingTransitionInvalidForState() {
        $this->expectException(CannotApplyTransitionException::class);
        $this->workflowStrategy->apply($this->resource, 'BC');
    }
}
