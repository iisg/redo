<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class ResourceUpdateContentsCommandValidator extends CommandAttributesValidator {
    /**
     * @param ResourceUpdateContentsCommand $command
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('resource', Validator::instance(ResourceEntity::class)->callback(function (ResourceEntity $r) {
                return $r->getId() > 0;
            }))
            ->attribute('contents', Validator::containsOnlyValuesForMetadataDefinedInResourceKind($command->getResource()->getKind())
                ->length(1));
    }
}
