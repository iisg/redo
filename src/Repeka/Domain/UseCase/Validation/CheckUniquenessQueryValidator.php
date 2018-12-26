<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validator;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;

class CheckUniquenessQueryValidator extends CommandAttributesValidator {

    private $resourceClassExistsRule;

    public function __construct(ResourceClassExistsRule $resourceClassExistsRule) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('metadataId', Validator::intVal())
            ->attribute('metadataValue', Validator::stringVal())
            ->attribute('resourceId', Validator::oneOf(Validator::intVal(), Validator::nullType()))
            ->attribute('resourceClass', $this->resourceClassExistsRule);
    }
}
