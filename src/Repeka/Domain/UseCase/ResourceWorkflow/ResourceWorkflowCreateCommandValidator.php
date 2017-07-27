<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
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

    /** @SuppressWarnings("PHPMD.LongVariable") */
    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ResourceClassExistsRule $resourceClassExistsRule,
        WorkflowTransitionsDefinitionIsValidRule $workflowTransitionsDefinitionIsValidRule,
        WorkflowPlacesDefinitionIsValidRule $workflowPlacesDefinitionIsValidRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->workflowTransitionsDefinitionIsValidRule = $workflowTransitionsDefinitionIsValidRule;
        $this->workflowPlacesDefinitionIsValidRule = $workflowPlacesDefinitionIsValidRule;
    }

    /**
     * @param ResourceWorkflowCreateCommand $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('name', $this->notBlankInAllLanguagesRule)
            ->attribute('places', $this->workflowPlacesDefinitionIsValidRule)
            ->attribute('transitions', $this->workflowTransitionsDefinitionIsValidRule)
            ->attribute('resourceClass', $this->resourceClassExistsRule);
    }
}
