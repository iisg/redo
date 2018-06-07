<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandValidator;
use Repeka\Domain\Validation\Rules\LockedMetadataValuesAreUnchangedRule;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Repeka\Domain\Validation\Rules\ResourceDoesNotContainDuplicatedFilenamesRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;
use Repeka\Tests\Traits\StubsTrait;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceTransitionCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceWorkflow|PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var  ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var ResourceKind|PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;
    /** @var User|PHPUnit_Framework_MockObject_MockObject */
    private $user;

    protected function setUp() {
        $this->user = $this->createMock(User::class);
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->expects($this->any())->method('getWorkflow')->willReturn($this->workflow);
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resource->method('getKind')->willReturn($this->resourceKind);
    }

    private function createValidator(
        bool $valueSetMatchesResourceKind,
        bool $metadataValuesSatisfyConstraints,
        bool $resourceContentsCorrectStructure,
        bool $resourceDoesNotContainDuplicatedFilenames,
        bool $lockedMetadataValuesAreUnchanged,
        ?TransitionPossibilityChecker $transitionPossibilityChecker = null
    ): ResourceTransitionCommandValidator {
        if (!$transitionPossibilityChecker) {
            $transitionPossibilityChecker = $this->createMock(TransitionPossibilityChecker::class);
            $transitionPossibilityChecker->method('check')->willReturn(new TransitionPossibilityCheckResult([], false, false));
        }
        $valueSetMatchesResourceKindRule = $this->createRuleWithFactoryMethodMock(
            ValueSetMatchesResourceKindRule::class,
            'forResourceKind',
            $valueSetMatchesResourceKind
        );
        $metadataValuesSatisfyConstraintsRule = $this->createRuleWithFactoryMethodMock(
            MetadataValuesSatisfyConstraintsRule::class,
            'forResourceKind',
            $metadataValuesSatisfyConstraints
        );
        $resourceContentsCorrectStructureRule = $this->createRuleMock(
            ResourceContentsCorrectStructureRule::class,
            $resourceContentsCorrectStructure
        );
        $resourceDoesNotContainDuplicatedFilenamesRule = $this->createRuleMock(
            ResourceDoesNotContainDuplicatedFilenamesRule::class,
            $resourceDoesNotContainDuplicatedFilenames
        );
        $lockedMetadataValuesAreUnchangedRule = $this->createRuleMock(
            LockedMetadataValuesAreUnchangedRule::class,
            $lockedMetadataValuesAreUnchanged
        );
        return new ResourceTransitionCommandValidator(
            $transitionPossibilityChecker,
            $valueSetMatchesResourceKindRule,
            $metadataValuesSatisfyConstraintsRule,
            $resourceContentsCorrectStructureRule,
            $resourceDoesNotContainDuplicatedFilenamesRule,
            $lockedMetadataValuesAreUnchangedRule
        );
    }

    public function testValid() {
        $validator = $this->createValidator(true, true, true, true, true);
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $this->resource->method('hasWorkflow')->willReturn(true);
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [1]),
                $this->createWorkflowPlaceMock('p2', []),
            ]
        );
        $transition = $this->configureTransition('t1', ['p2']);
        $command = new ResourceTransitionCommand(
            $this->resource,
            ResourceContents::empty(),
            $transition,
            $this->user
        );
        $validator->validate($command);
    }

    public function testValidWhenNoWorkflow() {
        $validator = $this->createValidator(true, true, true, true, true);
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $command = new ResourceTransitionCommand(
            $this->resource,
            ResourceContents::empty(),
            SystemTransition::CREATE()->toTransition($this->resourceKind),
            $this->user
        );
        $validator->validate($command);
    }

    public function testInvalidWhenInvalidTransition() {
        $validator = $this->createValidator(true, true, true, true, true);
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/transitionId/');
        $this->resource->method('getId')->willReturn(1);
        $this->resource->method('hasWorkflow')->willReturn(true);
        $this->workflow->expects($this->once())->method('getTransitions')
            ->willReturn([new ResourceWorkflowTransition([], [], [], [], 't1')]);
        $transition = $this->createWorkflowTransitionMock([], [], [], 't2');
        $command = new ResourceTransitionCommand(
            $this->resource,
            ResourceContents::empty(),
            $transition,
            $this->user
        );
        $validator->validate($command);
    }

    public function testInvalidWhenTransitionImpossible() {
        $this->expectException(InvalidCommandException::class);
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $transitionPossibilityChecker = $this->createMock(TransitionPossibilityChecker::class);
        $transitionPossibilityChecker->method('check')->willReturn(new TransitionPossibilityCheckResult([], true, true));
        $validator = $this->createValidator(true, true, true, true, true, $transitionPossibilityChecker);
        $transition = $this->configureTransition('t1');
        $command = new ResourceTransitionCommand(
            $this->resource,
            ResourceContents::empty(),
            $transition,
            $this->createMock(User::class)
        );
        $validator->validate($command);
    }

    private function configureTransition(string $id, array $tos = []): ResourceWorkflowTransition {
        $transition = $this->createMock(ResourceWorkflowTransition::class);
        $transition->method('getId')->willReturn($id);
        $transition->method('getToIds')->willReturn($tos);
        $this->workflow->method('getTransitions')->willReturn([$transition]);
        $this->workflow->method('getTransition')->willReturn($transition);
        return $transition;
    }

    public function testInvalidIfContentsDoNotMatchResourceKind() {
        $validator = $this->createValidator(false, true, true, true, true);
        $command = new ResourceTransitionCommand(
            new ResourceEntity($this->resourceKind, ResourceContents::empty()),
            ResourceContents::empty(),
            SystemTransition::UPDATE()->toTransition($this->resourceKind, $this->resource),
            $this->user
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenConstraintsNotSatisfied() {
        $validator = $this->createValidator(true, false, true, true, true);
        $command = new ResourceTransitionCommand(
            new ResourceEntity($this->resourceKind, ResourceContents::empty()),
            ResourceContents::empty(),
            SystemTransition::UPDATE()->toTransition($this->resourceKind, $this->resource),
            $this->user
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenInvalidContentStructure() {
        $validator = $this->createValidator(true, true, false, true, true);
        $command = new ResourceTransitionCommand(
            new ResourceEntity($this->resourceKind, ResourceContents::empty()),
            ResourceContents::empty(),
            SystemTransition::UPDATE()->toTransition($this->resourceKind, $this->resource),
            $this->user
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenResourceContainsDuplicatedFilenames() {
        $validator = $this->createValidator(true, true, true, false, true);
        $command = new ResourceTransitionCommand(
            new ResourceEntity($this->resourceKind, ResourceContents::empty()),
            ResourceContents::empty(),
            SystemTransition::UPDATE()->toTransition($this->resourceKind, $this->resource),
            $this->user
        );
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenLockedMetadataValuesAreChangeds() {
        $validator = $this->createValidator(true, true, true, true, false);
        $command = new ResourceTransitionCommand(
            new ResourceEntity($this->resourceKind, ResourceContents::empty()),
            ResourceContents::empty(),
            SystemTransition::UPDATE()->toTransition($this->resourceKind, $this->resource),
            $this->user
        );
        $this->assertFalse($validator->isValid($command));
    }
}
