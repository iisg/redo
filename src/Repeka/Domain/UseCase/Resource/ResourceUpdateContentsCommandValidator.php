<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceUpdateContentsCommandValidator extends CommandAttributesValidator {
    /** @param ResourceUpdateContentsCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute(
                'resource',
                Validator::instance(ResourceEntity::class)->callback(
                    function (ResourceEntity $r) {
                        return $r->getId() > 0 || $r->getId() == SystemResource::UNAUTHENTICATED_USER;
                    }
                )
            );
    }
}
