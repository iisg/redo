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
        return Validator::intType()->min(0)->validate($maxCount);
    }

    public function validateAll(Metadata $metadata, $maxCount, array $metadataValues) {
        if ($maxCount != 0) {
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
