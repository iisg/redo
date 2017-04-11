<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\ResourceWorkflow\NoSuchTransitionException;
use Repeka\Domain\Factory\ResourceWorkflowStrategy;

class ResourceWorkflowTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflow */
    private $workflow;
    private $resource;
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowStrategy */
    private $workflowStrategy;

    protected function setUp() {
        $this->workflow = new ResourceWorkflow(['EN' => 'Some workflow']);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->workflowStrategy = $this->createMock(ResourceWorkflowStrategy::class);
    }

    public function testSettingName() {
        $this->assertEquals(['EN' => 'Some workflow'], $this->workflow->getName());
    }

    public function testNoPlacesAndTransitionsByDefault() {
        $this->assertEmpty($this->workflow->getPlaces());
        $this->assertEmpty($this->workflow->getTransitions());
    }

    public function testUpdatingPlacesFromArray() {
        $this->workflow->update([
            ['label' => ['EN' => 'First place']],
            ['label' => ['EN' => 'Second place']],
        ], []);
        $this->assertCount(2, $this->workflow->getPlaces());
        $this->assertEquals(['EN' => 'First place'], $this->workflow->getPlaces()[0]->getLabel());
        $this->assertEquals(['EN' => 'Second place'], $this->workflow->getPlaces()[1]->getLabel());
    }

    public function testUpdatingPlacesFromInstance() {
        $this->workflow->update([new ResourceWorkflowPlace(['PL' => 'Test'])], []);
        $this->assertCount(1, $this->workflow->getPlaces());
        $this->assertEquals(['PL' => 'Test'], $this->workflow->getPlaces()[0]->getLabel());
    }

    public function testUpdatingPlacesIfAlreadyExists() {
        $this->workflow->update([['label' => ['EN' => 'First place']]], []);
        $this->workflow->update([['label' => ['EN' => 'Another place']]], []);
        $this->assertCount(1, $this->workflow->getPlaces());
        $this->assertEquals(['EN' => 'Another place'], $this->workflow->getPlaces()[0]->getLabel());
    }

    public function testUpdatingTransitionsFromArray() {
        $this->workflow->update([], [
            ['label' => ['EN' => 'First transition'], 'froms' => ['A'], 'tos' => ['B']],
            ['label' => ['EN' => 'Second transition'], 'froms' => ['B'], 'tos' => ['C']],
        ]);
        $this->assertCount(2, $this->workflow->getTransitions());
        $this->assertEquals(['EN' => 'First transition'], $this->workflow->getTransitions()[0]->getLabel());
        $this->assertEquals(['A'], $this->workflow->getTransitions()[0]->getFromIds());
        $this->assertEquals(['B'], $this->workflow->getTransitions()[0]->getToIds());
        $this->assertEquals(['EN' => 'Second transition'], $this->workflow->getTransitions()[1]->getLabel());
        $this->assertEquals(['B'], $this->workflow->getTransitions()[1]->getFromIds());
        $this->assertEquals(['C'], $this->workflow->getTransitions()[1]->getToIds());
    }

    public function testUpdatingThumbnailAndDiagram() {
        $this->workflow->update([], [], 'json', 'png');
        $this->assertEquals('json', $this->workflow->getDiagram());
        $this->assertEquals('png', $this->workflow->getThumbnail());
    }

    public function testThrowsExceptionWhenNoStrategy() {
        $this->expectException(InvalidArgumentException::class);
        $this->workflow->apply($this->resource, 'letsgo');
    }

    public function testApplyingTransition() {
        $this->workflow->setWorkflowStrategy($this->workflowStrategy);
        $this->workflowStrategy->expects($this->once())->method('apply')->with($this->resource, 'letsgo')->willReturnArgument(0);
        $this->workflow->apply($this->resource, 'letsgo');
    }

    public function testMovingToState() {
        $this->workflow->setWorkflowStrategy($this->workflowStrategy);
        $this->workflowStrategy->expects($this->once())->method('setCurrentPlaces')
            ->with($this->resource, ['thestate'])->willReturnArgument(0);
        $this->workflow->setCurrentPlaces($this->resource, ['thestate']);
    }

    public function testGettingTransitionsForResource() {
        $this->workflow->update([], [
            new ResourceWorkflowTransition([], [], [], [], 'onetransition'),
            new ResourceWorkflowTransition([], [], [], [], 'twotransition'),
            new ResourceWorkflowTransition([], [], [], [], 'anothertransition'),
        ]);
        $this->workflow->setWorkflowStrategy($this->workflowStrategy);
        $this->workflowStrategy->expects($this->once())->method('getTransitions')->with($this->resource)
            ->willReturn(['onetransition', 'anothertransition']);
        $availableTransitions = $this->workflow->getTransitions($this->resource);
        $this->assertCount(2, $availableTransitions);
        $this->assertEquals('onetransition', $availableTransitions[0]->getId());
        $this->assertEquals('anothertransition', $availableTransitions[1]->getId());
    }

    public function testGettingPlacesForResource() {
        $this->workflow->update([
            new ResourceWorkflowPlace([], 'first'),
            new ResourceWorkflowPlace([], 'second'),
            new ResourceWorkflowPlace([], 'third'),
        ], []);
        $this->workflow->setWorkflowStrategy($this->workflowStrategy);
        $this->workflowStrategy->expects($this->once())->method('getPlaces')->with($this->resource)
            ->willReturn(['first', 'third']);
        $currentPlaces = $this->workflow->getPlaces($this->resource);
        $this->assertCount(2, $currentPlaces);
        $this->assertEquals('first', $currentPlaces[0]->getId());
        $this->assertEquals('third', $currentPlaces[1]->getId());
    }

    public function testGettingPermittedTransitions() {
        $this->workflow->update([], [
            new ResourceWorkflowTransition([], [], [], ['A'], 'first'),
            new ResourceWorkflowTransition([], [], [], ['B'], 'second'),
        ]);
        $this->workflow->setWorkflowStrategy($this->workflowStrategy);
        $this->workflowStrategy->expects($this->once())->method('getTransitions')->with($this->resource)
            ->willReturn(['first', 'second']);
        $user = $this->createMock(User::class);
        $user->method('hasRole')->willReturnCallback(function ($role) {
            return $role == 'A';
        });
        $permittedTransitions = $this->workflow->getPermittedTransitions($this->resource, $user);
        $this->assertCount(1, $permittedTransitions);
        $this->assertEquals('first', $permittedTransitions[0]->getId());
    }

    public function testGettingTransitionById() {
        $this->workflow->update([], [
            new ResourceWorkflowTransition([], [], [], ['A'], 'first'),
            new ResourceWorkflowTransition([], [], [], ['B'], 'second'),
        ]);
        $transition = $this->workflow->getTransition('first');
        $this->assertNotNull($transition);
        $this->assertEquals('first', $transition->getId());
    }

    public function testGettingNotExistentTransition() {
        $this->expectException(NoSuchTransitionException::class);
        $this->workflow->getTransition('a');
    }
}
