<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class ResourceCreateCommandValidator extends CommandAttributesValidator {
    /**
     * @param ResourceCreateCommand $command
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('kind', Validator::instance(ResourceKind::class)->callback(function (ResourceKind $rk) {
                return $rk->getId() > 0;
            }))
            ->attribute('contents', Validator::containsOnlyValuesForMetadataDefinedInResourceKind($command->getKind())->length(1));
    }
}
