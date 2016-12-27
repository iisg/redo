<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class ResourceCreateCommandValidator extends CommandAttributesValidator {
    /**
     * @param ResourceCreateCommand $command
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        $metadataIdValidators = array_map(function (Metadata $metadata) {
            return Validator::key($metadata->getId(), null, false);
        }, $command->getKind()->getMetadataList());
        $contentsValidator = call_user_func_array([Validator::arrayType()->length(1), 'keySet'], $metadataIdValidators);
        return Validator
            ::attribute('kind', Validator::instance(ResourceKind::class)->callback(function (ResourceKind $rk) {
                return $rk->getId() > 0;
            }))
            ->attribute('contents', $contentsValidator);
    }
}
