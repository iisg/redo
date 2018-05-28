<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\NoAssigneeMetadataInFirstPlaceRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesForDeletionAreUnoccupiedRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsDefinitionIsValidRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings("PHPMD.LongVariable") */
class ResourceWorkflowUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflow */
    private $workflow;
    /** @var ResourceWorkflowUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsRule);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowPlacesForDeletionAreUnoccupiedRule = $this->createRuleMock(WorkflowPlacesForDeletionAreUnoccupiedRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", true);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, true);
        $this->validator = new ResourceWorkflowUpdateCommandValidator(
            $entityExistsRule,
            $notBlankInAllLanguagesRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowPlacesForDeletionAreUnoccupiedRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule
        );
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->workflow->expects($this->any())->method('getId')->willReturn(1);
    }

    public function testValid() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [['label' => []]], [], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidBecauseOfNotSavedWorkflow() {
        $command = new ResourceWorkflowUpdateCommand($this->createMock(ResourceWorkflow::class), [], [], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenTransitionLabelsDoNotMatch() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsRule);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowPlacesForDeletionAreUnoccupiedRule = $this->createRuleMock(WorkflowPlacesForDeletionAreUnoccupiedRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", true);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, true);
        $validator = new ResourceWorkflowUpdateCommandValidator(
            $entityExistsRule,
            $notBlankInAllLanguagesRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowPlacesForDeletionAreUnoccupiedRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule
        );
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [], [], null, null);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenAssigneeMetadataIsSet() {
        $place = $this->createWorkflowPlaceMock(1, [], [2]);
        $entityExistsRule = $this->createEntityExistsMock(true);
        $entityExistsMock = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $workflowPlacesForDeletionAreUnoccupiedRule = $this->createRuleMock(WorkflowPlacesForDeletionAreUnoccupiedRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsMock);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", false);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, false);
        $validator = new ResourceWorkflowUpdateCommandValidator(
            $entityExistsRule,
            $notBlankInAllLanguagesRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowPlacesForDeletionAreUnoccupiedRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule
        );
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [$place], [], null, null);
        $this->assertFalse($validator->isValid($command));
    }
}
