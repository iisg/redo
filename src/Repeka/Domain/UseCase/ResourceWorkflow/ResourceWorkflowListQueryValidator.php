<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowListQueryValidator extends CommandAttributesValidator {

    /** @var ResourceClassExistsRule */
    private $resourceClassExistsRule;

    public function __construct(ResourceClassExistsRule $resourceClassExistsRule) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    /**
     * @param ResourceWorkflowListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resourceClass', Validator::optional($this->resourceClassExistsRule))
            ->setName('Resource class is not defined');
    }
}
