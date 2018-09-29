<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceCloneCommandValidator extends CommandAttributesValidator {
    /** @param ResourceCloneCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute(
                'kind',
                Validator::instance(ResourceKind::class)->callback(
                    function (ResourceKind $rk) {
                        return $rk->getId() != 0;
                    }
                )
            )
            ->attribute(
                'resource',
                Validator::allOf(
                    Validator::notEmpty(),
                    Validator::instance(ResourceEntity::class),
                    Validator::callback(
                        function (ResourceEntity $resource) {
                            return $resource->getId() > 0;
                        }
                    )
                )
            );
    }
}
