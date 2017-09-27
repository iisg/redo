<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends ResourceKindCreateCommandValidator {
    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator::allOf(
            parent::getValidator($command),
            Validator::attribute('resourceKindId', Validator::intVal()->min(1))
        );
    }
}
