<?php
namespace Domain\Workflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Workflow\ResourceWorkflowTransitionHelper;

/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class ResourceWorkflowTransitionHelperTest extends \PHPUnit_Framework_TestCase {
    public function testGettingPlacesPermittedByResourceMetadata() {
        $workflow = $this->createWorkflowMock([
            $this->createPlaceMock('', false),
            $expected = $this->createPlaceMock('', true),
            $this->createPlaceMock('', false),
        ], []);
        $helper = new ResourceWorkflowTransitionHelper($workflow);
        $this->assertEquals(
            [$expected],
            $helper->getPlacesPermittedByResourceMetadata($this->createMock(ResourceEntity::class))
        );
    }

    public function testCheckingPlacePermission() {
        $workflow = $this->createWorkflowMock([
            $this->createPlaceMock('', false),
            $this->createPlaceMock('THIS', true),
            $this->createPlaceMock('', false),
        ], []);
        $helper = new ResourceWorkflowTransitionHelper($workflow);
        $this->assertTrue($helper->placeIsPermittedByResourceMetadata('THIS', $this->createMock(ResourceEntity::class)));
        $this->assertFalse($helper->placeIsPermittedByResourceMetadata('THAT', $this->createMock(ResourceEntity::class)));
    }

    public function testGettingTransitionsPermittedByRole() {
        $workflow = $this->createWorkflowMock([], [
            $this->createTransitionMock('', false),
            $expected = $this->createTransitionMock('', true),
            $this->createTransitionMock('', false),
        ]);
        $helper = new ResourceWorkflowTransitionHelper($workflow);
        $this->assertEquals(
            [$expected],
            $helper->getTransitionsPermittedByRole($this->createMock(User::class))
        );
    }

    public function testGettingPossibleTransitions() {
        $workflow = $this->createWorkflowMock([
            $this->createPlaceMock('', false),
            $this->createPlaceMock('', true),
        ], [
            $this->createTransitionMock('', false, false),
            $this->createTransitionMock('', false, true),
            $this->createTransitionMock('', true, false),
            $expected = $this->createTransitionMock('', true, true),
        ]);
        $helper = new ResourceWorkflowTransitionHelper($workflow);
        $this->assertEquals([$expected], $helper->getPossibleTransitions(
            $this->createMock(ResourceEntity::class),
            $this->createMock(User::class)
        ));
    }

    public function testCheckingTransitionPossibility() {
        $workflow = $this->createWorkflowMock([
            $this->createPlaceMock('', false),
            $this->createPlaceMock('', true),
        ], [
            $this->createTransitionMock('', false, false),
            $this->createTransitionMock('', false, true),
            $this->createTransitionMock('', true, false),
            $this->createTransitionMock('THIS', true, true),
        ]);
        $helper = new ResourceWorkflowTransitionHelper($workflow);
        $this->assertTrue($helper->transitionIsPossible('THIS', $this->createMock(ResourceEntity::class), $this->createMock(User::class)));
        $this->assertFalse($helper->transitionIsPossible('THAT', $this->createMock(ResourceEntity::class), $this->createMock(User::class)));
    }

    /** @return ResourceWorkflowPlace|\PHPUnit_Framework_MockObject_MockObject */
    private function createPlaceMock(string $id, bool $requiredMetadataFilled): ResourceWorkflowPlace {
        $mock = $this->createMock(ResourceWorkflowPlace::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('isRequiredMetadataFilled')->willReturn($requiredMetadataFilled);
        return $mock;
    }

    /** @return ResourceWorkflowTransition|\PHPUnit_Framework_MockObject_MockObject */
    private function createTransitionMock(
        string $id,
        bool $userHasRoleRequiredToApply,
        bool $canEnterTos = true
    ): ResourceWorkflowTransition {
        $mock = $this->createMock(ResourceWorkflowTransition::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('userHasRoleRequiredToApply')->willReturn($userHasRoleRequiredToApply);
        $mock->method('canEnterTos')->willReturn($canEnterTos);
        return $mock;
    }

    /** @return ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private function createWorkflowMock(array $places, array $transitions): ResourceWorkflow {
        $mock = $this->createMock(ResourceWorkflow::class);
        $mock->method('getPlaces')->willReturn($places);
        $mock->method('getTransitions')->willReturn($transitions);
        return $mock;
    }
}
