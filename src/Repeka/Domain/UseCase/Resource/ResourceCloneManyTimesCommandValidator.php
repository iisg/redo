<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceCloneManyTimesCommandValidator extends CommandAttributesValidator {
    /** @param ResourceCloneManyTimesCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator::attribute('cloneTimes', Validator::intVal()->between(1, 50));
    }
}
