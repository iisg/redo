<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class MaxCountConstraint extends AbstractMetadataConstraint {
    public function getSupportedControls(): array {
        return MetadataControl::toArray();
    }

    public function isConfigValid($maxCount): bool {
        return isset($maxCount)
            ? Validator::oneOf(
                Validator::intType()->min(1),
                Validator::intType()->equals(-1)
            )->validate($maxCount)
            : true;
    }

    public function validateAll(Metadata $metadata, $maxCount, array $metadataValues) {
        if (isset($maxCount) && $maxCount !== -1) {
            try {
                Validator::length(0, $maxCount)->setName($metadata->getName())->assert($metadataValues);
            } catch (NestedValidationException $e) {
                throw new RespectValidationFailedException($e);
            }
        }
    }

    public function validateSingle(Metadata $metadata, $config, $metadataValue) {
        throw new \BadMethodCallException('This validator can validate only the whole array of metadata');
    }
}
