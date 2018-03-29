<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\WorkflowPlacesForDeletionAreUnoccupiedRule;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
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

    public function __construct(
        EntityExistsRule $entityExistsRule,
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        WorkflowTransitionsDefinitionIsValidRule $workflowTransitionsDefinitionIsValidRule,
        WorkflowPlacesDefinitionIsValidRule $workflowPlacesDefinitionIsValidRule,
        WorkflowPlacesForDeletionAreUnoccupiedRule $workflowPlacesForDeletionAreUnoccupied
    ) {
        $this->entityExistsRule = $entityExistsRule;
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->workflowTransitionsDefinitionIsValidRule = $workflowTransitionsDefinitionIsValidRule;
        $this->workflowPlacesDefinitionIsValidRule = $workflowPlacesDefinitionIsValidRule;
        $this->workflowPlacesForDeletionAreUnoccupied = $workflowPlacesForDeletionAreUnoccupied;
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
                ->attribute('places', $this->workflowPlacesForDeletionAreUnoccupied->forWorkflow($command->getWorkflow()))
                ->attribute('transitions', $this->workflowTransitionsDefinitionIsValidRule)
        );
    }
}
