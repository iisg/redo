<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceListQueryValidator extends CommandAttributesValidator {
    /** @var  ResourceClassExistsRule */
    private $resourceClassExistsRule;

    public function __construct(ResourceClassExistsRule $resourceClassExistsRule) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    /**
     * @param ResourceListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resourceClass', $this->resourceClassExistsRule);
    }
}