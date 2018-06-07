<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\NoAssigneeMetadataInFirstPlaceRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsDefinitionIsValidRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var ResourceClassExistsRule */
    private $resourceClassExistsRule;
    /** @var WorkflowTransitionsDefinitionIsValidRule
     * @SuppressWarnings("PHPMD.LongVariable")
     */
    private $workflowTransitionsDefinitionIsValidRule;
    /** @var WorkflowPlacesDefinitionIsValidRule */
    private $workflowPlacesDefinitionIsValidRule;
    /** @var WorkflowTransitionNamesMatchInAllLanguagesRule */
    private $workflowTransitionNamesMatchInAllLanguagesRule;
    /** @var WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule */
    private $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
    /** @var NoAssigneeMetadataInFirstPlaceRule */
    private $noAssigneeMetadataInFirstPlaceRule;

    /** @SuppressWarnings("PHPMD.LongVariable") */
    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ResourceClassExistsRule $resourceClassExistsRule,
        WorkflowTransitionsDefinitionIsValidRule $workflowTransitionsDefinitionIsValidRule,
        WorkflowPlacesDefinitionIsValidRule $workflowPlacesDefinitionIsValidRule,
        WorkflowTransitionNamesMatchInAllLanguagesRule $workflowTransitionNamesMatchInAllLanguagesRule,
        NoAssigneeMetadataInFirstPlaceRule $noAssigneeMetadataInFirstPlaceRule,
        WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->workflowTransitionsDefinitionIsValidRule = $workflowTransitionsDefinitionIsValidRule;
        $this->workflowPlacesDefinitionIsValidRule = $workflowPlacesDefinitionIsValidRule;
        $this->workflowTransitionNamesMatchInAllLanguagesRule = $workflowTransitionNamesMatchInAllLanguagesRule;
        $this->noAssigneeMetadataInFirstPlaceRule = $noAssigneeMetadataInFirstPlaceRule;
        $this->workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule = $workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule;
    }

    /**
     * @param ResourceWorkflowCreateCommand $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('name', $this->notBlankInAllLanguagesRule)
            ->attribute('places', $this->workflowPlacesDefinitionIsValidRule)
            ->attribute('places', $this->noAssigneeMetadataInFirstPlaceRule)
            ->attribute('transitions', $this->workflowTransitionsDefinitionIsValidRule)
            ->attribute('resourceClass', $this->resourceClassExistsRule)
            ->attribute('transitions', $this->workflowTransitionNamesMatchInAllLanguagesRule->withPlaces($command->getPlaces()))
            ->attribute(
                'transitions',
                $this->workflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRule->withPlaces($command->getPlaces())
            );
    }
}
