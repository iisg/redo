<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Domain\Validation\Rules\NoAssigneeMetadataInFirstPlaceRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesForDeletionAreUnoccupiedRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsDefinitionIsValidRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

/** @SuppressWarnings("PHPMD.LongVariable") */
class ResourceWorkflowUpdateCommandValidator extends CommandAttributesValidator {
    /** @var EntityExistsRule */
    private $entityExistsRule;
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var WorkflowTransitionsDefinitionIsValidRule */
    protected $workflowTransitionsDefinitionIsValidRule;
    /** @var WorkflowPlacesDefinitionIsValidRule */
    protected $workflowPlacesDefinitionIsValidRule;
    /** @var WorkflowPlacesForDeletionAreUnoccupiedRule */
    protected $workflowPlacesForDeletionAreUnoccupied;
    /** @var WorkflowTransitionNamesMatchInAllLanguagesRule */
    private $workflowTransitionNamesMatchInAllLanguagesRule;
    /** @var WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule */
    private $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
    /** @var NoAssigneeMetadataInFirstPlaceRule */
    private $noAssigneeMetadataInFirstPlaceRule;

    public function __construct(
        EntityExistsRule $entityExistsRule,
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        WorkflowTransitionsDefinitionIsValidRule $workflowTransitionsDefinitionIsValidRule,
        WorkflowPlacesDefinitionIsValidRule $workflowPlacesDefinitionIsValidRule,
        WorkflowPlacesForDeletionAreUnoccupiedRule $workflowPlacesForDeletionAreUnoccupied,
        WorkflowTransitionNamesMatchInAllLanguagesRule $workflowTransitionNamesMatchInAllLanguagesRule,
        NoAssigneeMetadataInFirstPlaceRule $noAssigneeMetadataInFirstPlaceRule,
        WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule
    ) {
        $this->entityExistsRule = $entityExistsRule;
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->workflowTransitionsDefinitionIsValidRule = $workflowTransitionsDefinitionIsValidRule;
        $this->workflowPlacesDefinitionIsValidRule = $workflowPlacesDefinitionIsValidRule;
        $this->workflowPlacesForDeletionAreUnoccupied = $workflowPlacesForDeletionAreUnoccupied;
        $this->workflowTransitionNamesMatchInAllLanguagesRule = $workflowTransitionNamesMatchInAllLanguagesRule;
        $this->noAssigneeMetadataInFirstPlaceRule = $noAssigneeMetadataInFirstPlaceRule;
        $this->workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule = $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
    }

    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator::allOf(
            Validator::attribute(
                'workflow',
                Validator::instance(ResourceWorkflow::class)
                    ->callback(
                        function (ResourceWorkflow $workflow) {
                            return $workflow->getId() > 0;
                        }
                    )
            )
                ->attribute('name', $this->notBlankInAllLanguagesRule)
                ->attribute('places', $this->workflowPlacesDefinitionIsValidRule)
                ->attribute('places', $this->noAssigneeMetadataInFirstPlaceRule)
                ->attribute('places', $this->workflowPlacesForDeletionAreUnoccupied->forWorkflow($command->getWorkflow()))
                ->attribute('transitions', $this->workflowTransitionsDefinitionIsValidRule)
                ->attribute('transitions', $this->workflowTransitionNamesMatchInAllLanguagesRule->withPlaces($command->getPlaces()))
                ->attribute(
                    'transitions',
                    $this->workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule->withPlaces($command->getPlaces())
                )
        );
    }
}
