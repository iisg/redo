<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Exception\ResourceWorkflow\NoSuchTransitionException;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;
use Repeka\Tests\Traits\StubsTrait;

class ResourceWorkflowTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflow */
    private $workflow;
    private $resource;
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowDriver */
    private $workflowDriver;

    protected function setUp() {
        $this->workflow = new ResourceWorkflow(['EN' => 'Some workflow'], [], [], 'books');
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->workflowDriver = $this->createMock(ResourceWorkflowDriver::class);
    }

    public function testSettingName() {
        $this->assertEquals(['EN' => 'Some workflow'], $this->workflow->getName());
    }

    public function testSettingResourceClass() {
        $this->assertEquals('books', $this->workflow->getResourceClass());
    }

    public function testNoPlacesAndTransitionsByDefault() {
        $this->assertEmpty($this->workflow->getPlaces());
        $this->assertEmpty($this->workflow->getTransitions());
    }

    public function testUpdatingName() {
        $this->workflow->update(['EN' => 'New name'], [], [], 'books');
        $this->assertEquals(['EN' => 'New name'], $this->workflow->getName());
    }

    public function testUpdatingPlacesFromArray() {
        $this->workflow->update([], [
            ['label' => ['EN' => 'First place']],
            ['label' => ['EN' => 'Second place']],
        ], [], 'books');
        $this->assertCount(2, $this->workflow->getPlaces());
        $this->assertEquals(['EN' => 'First place'], $this->workflow->getPlaces()[0]->getLabel());
        $this->assertEquals(['EN' => 'Second place'], $this->workflow->getPlaces()[1]->getLabel());
    }

    public function testUpdatingPlacesFromInstance() {
        $this->workflow->update([], [new ResourceWorkflowPlace(['PL' => 'Test'])], [], 'books');
        $this->assertCount(1, $this->workflow->getPlaces());
        $this->assertEquals(['PL' => 'Test'], $this->workflow->getPlaces()[0]->getLabel());
    }

    public function testInitialPlaceIsTheFirstPlace() {
        $place1 = new ResourceWorkflowPlace(['PL' => 'First']);
        $place2 = new ResourceWorkflowPlace(['PL' => 'Second']);
        $this->workflow->update([], [$place1, $place2], [], 'books');
        $this->assertCount(2, $this->workflow->getPlaces());
        $this->assertSame('First', $this->workflow->getInitialPlace()->getLabel()['PL']);
    }

    public function testUpdatingPlacesIfAlreadyExists() {
        $this->workflow->update([], [['label' => ['EN' => 'First place']]], [], 'books');
        $this->workflow->update([], [['label' => ['EN' => 'Another place']]], [], 'books');
        $this->assertCount(1, $this->workflow->getPlaces());
        $this->assertEquals(['EN' => 'Another place'], $this->workflow->getPlaces()[0]->getLabel());
    }

    public function testUpdatingTransitionsFromArray() {
        $this->workflow->update([], [], [
            ['label' => ['EN' => 'First transition'], 'froms' => ['A'], 'tos' => ['B']],
            ['label' => ['EN' => 'Second transition'], 'froms' => ['B'], 'tos' => ['C']],
        ], 'books');
        $this->assertCount(2, $this->workflow->getTransitions());
        $this->assertEquals(['EN' => 'First transition'], $this->workflow->getTransitions()[0]->getLabel());
        $this->assertEquals(['A'], $this->workflow->getTransitions()[0]->getFromIds());
        $this->assertEquals(['B'], $this->workflow->getTransitions()[0]->getToIds());
        $this->assertEquals(['EN' => 'Second transition'], $this->workflow->getTransitions()[1]->getLabel());
        $this->assertEquals(['B'], $this->workflow->getTransitions()[1]->getFromIds());
        $this->assertEquals(['C'], $this->workflow->getTransitions()[1]->getToIds());
    }

    public function testUpdatingThumbnailAndDiagram() {
        $this->workflow->update([], [], [], 'json', 'png');
        $this->assertEquals('json', $this->workflow->getDiagram());
        $this->assertEquals('png', $this->workflow->getThumbnail());
    }

    public function testThrowsExceptionWhenNoDriver() {
        $this->expectException(InvalidArgumentException::class);
        $this->workflow->apply($this->resource, 'letsgo');
    }

    public function testApplyingTransition() {
        $this->workflow->setWorkflowDriver($this->workflowDriver);
        $this->workflowDriver->expects($this->once())->method('apply')->with($this->resource, 'letsgo')->willReturnArgument(0);
        $this->workflow->apply($this->resource, 'letsgo');
    }

    public function testMovingToState() {
        $this->workflow->setWorkflowDriver($this->workflowDriver);
        $this->workflowDriver->expects($this->once())->method('setCurrentPlaces')
            ->with($this->resource, ['thestate'])->willReturnArgument(0);
        $this->workflow->setCurrentPlaces($this->resource, ['thestate']);
    }

    public function testGettingTransitionsForResource() {
        $this->workflow->update([], [], [
            new ResourceWorkflowTransition([], [], [], [], 'onetransition'),
            new ResourceWorkflowTransition([], [], [], [], 'twotransition'),
            new ResourceWorkflowTransition([], [], [], [], 'anothertransition'),
        ], 'books');
        $this->workflow->setWorkflowDriver($this->workflowDriver);
        $this->workflowDriver->expects($this->once())->method('getTransitions')->with($this->resource)
            ->willReturn(['onetransition', 'anothertransition']);
        $availableTransitions = $this->workflow->getTransitions($this->resource);
        $this->assertCount(2, $availableTransitions);
        $this->assertEquals('onetransition', $availableTransitions[0]->getId());
        $this->assertEquals('anothertransition', $availableTransitions[1]->getId());
    }

    public function testGettingPlacesForResource() {
        $this->workflow->update([], [
            new ResourceWorkflowPlace([], 'first'),
            new ResourceWorkflowPlace([], 'second'),
            new ResourceWorkflowPlace([], 'third'),
        ], [], 'books');
        $this->workflow->setWorkflowDriver($this->workflowDriver);
        $this->workflowDriver->expects($this->once())->method('getPlaces')->with($this->resource)
            ->willReturn(['first', 'third']);
        $currentPlaces = $this->workflow->getPlaces($this->resource);
        $this->assertCount(2, $currentPlaces);
        $this->assertEquals('first', $currentPlaces[0]->getId());
        $this->assertEquals('third', $currentPlaces[1]->getId());
    }

    public function testGettingTransitionById() {
        $this->workflow->update([], [], [
            new ResourceWorkflowTransition([], [], [], ['A'], 'first'),
            new ResourceWorkflowTransition([], [], [], ['B'], 'second'),
        ], 'books');
        $transition = $this->workflow->getTransition('first');
        $this->assertNotNull($transition);
        $this->assertEquals('first', $transition->getId());
    }

    public function testGettingNotExistentTransition() {
        $this->expectException(NoSuchTransitionException::class);
        $this->workflow->getTransition('a');
    }
}
