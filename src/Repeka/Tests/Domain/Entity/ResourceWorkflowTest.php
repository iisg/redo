<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\ResourceWorkflow\NoSuchTransitionException;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;

class ResourceWorkflowTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflow */
    private $workflow;
    private $resource;
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowDriver */
    private $workflowDriver;

    protected function setUp() {
        $this->workflow = new ResourceWorkflow(['EN' => 'Some workflow']);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->workflowDriver = $this->createMock(ResourceWorkflowDriver::class);
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
        $this->workflow->update([], [
            new ResourceWorkflowTransition([], [], [], [], 'onetransition'),
            new ResourceWorkflowTransition([], [], [], [], 'twotransition'),
            new ResourceWorkflowTransition([], [], [], [], 'anothertransition'),
        ]);
        $this->workflow->setWorkflowDriver($this->workflowDriver);
        $this->workflowDriver->expects($this->once())->method('getTransitions')->with($this->resource)
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
        $this->workflow->setWorkflowDriver($this->workflowDriver);
        $this->workflowDriver->expects($this->once())->method('getPlaces')->with($this->resource)
            ->willReturn(['first', 'third']);
        $currentPlaces = $this->workflow->getPlaces($this->resource);
        $this->assertCount(2, $currentPlaces);
        $this->assertEquals('first', $currentPlaces[0]->getId());
        $this->assertEquals('third', $currentPlaces[1]->getId());
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

    public function testGettingUnsatisfiedTransitionExplanations() {
        $workflow = $this->createPartiallyStubbedWorkflow([
            $this->createPlaceMock('nothingRequired'),
            $this->createPlaceMock('somethingMissing', [1]),
        ], [
            $this->createTransitionMock('possible1', true),
            $this->createTransitionMock('possible2', true, ['nothingRequired']),
            $inactive1 = $this->createTransitionMock('missingRoles', false),
            $inactive2 = $this->createTransitionMock('missingMetadata', true, ['somethingMissing']),
        ]);
        $user = $this->createMock(User::class);
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getContents')->willReturn([]);
        $result = $workflow->getUnsatisfiedTransitionExplanations($resource, $user);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey($inactive1->getId(), $result);
        $this->assertArrayHasKey($inactive2->getId(), $result);
    }

    public function testCheckingTransitionPossibility() {
        $workflow = $this->createPartiallyStubbedWorkflow([
            $this->createPlaceMock('nothingRequired'),
            $this->createPlaceMock('somethingMissing', [1]),
        ], [
            $this->createTransitionMock('possible1', true),
            $this->createTransitionMock('possible2', true, ['nothingRequired']),
            $this->createTransitionMock('missingRoles', false),
            $this->createTransitionMock('missingMetadata', true, ['somethingMissing']),
        ]);
        $user = $this->createMock(User::class);
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getContents')->willReturn([]);
        $this->assertTrue($workflow->isTransitionPossible('possible1', $resource, $user));
        $this->assertTrue($workflow->isTransitionPossible('possible2', $resource, $user));
        $this->assertFalse($workflow->isTransitionPossible('missingRoles', $resource, $user));
        $this->assertFalse($workflow->isTransitionPossible('missingMetadata', $resource, $user));
    }

    /** @return ResourceWorkflowPlace|\PHPUnit_Framework_MockObject_MockObject */
    private function createPlaceMock(string $id = '', array $missingMetadata = []): ResourceWorkflowPlace {
        $mock = $this->createMock(ResourceWorkflowPlace::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('getMissingRequiredMetadataIds')->willReturn($missingMetadata);
        $mock->method('resourceHasRequiredMetadata')->willReturn(empty($missingMetadata));
        return $mock;
    }

    /** @return ResourceWorkflowTransition|\PHPUnit_Framework_MockObject_MockObject */
    private function createTransitionMock(string $id, bool $userHasRoleRequiredToApply, array $tos = []): ResourceWorkflowTransition {
        $mock = $this->createMock(ResourceWorkflowTransition::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('userHasRoleRequiredToApply')->willReturn($userHasRoleRequiredToApply);
        $mock->method('getToIds')->willReturn($tos);
        return $mock;
    }

    /** @return ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private function createPartiallyStubbedWorkflow(array $places, array $transitions) {
        $mock = $this->getMockBuilder(ResourceWorkflow::class)
            ->setMethods(['getPlaces', 'getTransitions', 'getTransition'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('getPlaces')->willReturn($places);
        $mock->method('getTransitions')->willReturn($transitions);
        $transitionIds = array_map(function (ResourceWorkflowTransition $transition) {
            return $transition->getId();
        }, $transitions);
        $mock->method('getTransition')->willReturnCallback(function ($id) use ($transitions, $transitionIds) {
            return array_combine($transitionIds, $transitions)[$id];
        });
        return $mock;
    }
}
