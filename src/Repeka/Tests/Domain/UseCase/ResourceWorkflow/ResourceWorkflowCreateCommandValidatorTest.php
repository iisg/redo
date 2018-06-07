<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommandValidator;
use Repeka\Domain\Validation\Rules\NoAssigneeMetadataInFirstPlaceRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsDefinitionIsValidRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings("PHPMD.LongVariable") */
class ResourceWorkflowCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflowCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $resourceClassExistsRule = $this->createRuleMock(ResourceClassExistsRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsRule);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", true);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, true);
        $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule::class, "withPlaces", true);
        $this->validator = new ResourceWorkflowCreateCommandValidator(
            $notBlankInAllLanguagesRule,
            $resourceClassExistsRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule,
            $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule
        );
    }

    public function testValid() {
        $command = new ResourceWorkflowCreateCommand([], [['label' => []]], [], 'books', null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWhenNameLanguagesDoNotMatchSystemLanguages() {
        $entityExistsMock = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, false);
        $resourceClassExistsRule = $this->createRuleMock(ResourceClassExistsRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsMock);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", true);
        $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule::class, "withPlaces", true);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, true);
        $validator = new ResourceWorkflowCreateCommandValidator(
            $notBlankInAllLanguagesRule,
            $resourceClassExistsRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule,
            $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule
        );
        $command = new ResourceWorkflowCreateCommand([], [], [], 'books', null, null);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenTransitionLabelsDoNotMatch() {
        $entityExistsMock = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $resourceClassExistsRule = $this->createRuleMock(ResourceClassExistsRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsMock);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", false);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, true);
        $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule::class, "withPlaces", true);
        $validator = new ResourceWorkflowCreateCommandValidator(
            $notBlankInAllLanguagesRule,
            $resourceClassExistsRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule,
            $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule
        );
        $command = new ResourceWorkflowCreateCommand([], [], [], 'books', null, null);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenAssigneeMetadataIsSet() {
        $place = $this->createWorkflowPlaceMock(1, [], [2]);
        $entityExistsMock = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $resourceClassExistsRule = $this->createRuleMock(ResourceClassExistsRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsMock);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", false);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, false);
        $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule::class, "withPlaces", true);
        $validator = new ResourceWorkflowCreateCommandValidator(
            $notBlankInAllLanguagesRule,
            $resourceClassExistsRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule,
            $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule
        );
        $command = new ResourceWorkflowCreateCommand([], [$place], [], 'books', null, null);
        $this->assertFalse($validator->isValid($command));
    }
}
