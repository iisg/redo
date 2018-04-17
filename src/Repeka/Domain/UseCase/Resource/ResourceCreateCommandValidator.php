<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceCreateCommandValidator extends CommandAttributesValidator {
    /** @param ResourceCreateCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute(
                'kind',
                Validator::instance(ResourceKind::class)->callback(
                    function (ResourceKind $rk) {
                        return $rk->getId() != 0;
                    }
                )
            );
    }
}
