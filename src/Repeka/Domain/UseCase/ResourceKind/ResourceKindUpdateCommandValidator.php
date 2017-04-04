<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends ResourceKindCreateCommandValidator {
    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validator {
        return parent::getValidator($command)
            ->attribute('resourceKindId', Validator::intVal()->min(1));
    }
}
