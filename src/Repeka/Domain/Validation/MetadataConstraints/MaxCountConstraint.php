<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class MaxCountConstraint extends AbstractMetadataConstraint implements ConfigurableMetadataConstraint {
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

    /** @inheritdoc */
    public function validateAll(Metadata $metadata, array $metadataValues, ResourceEntity $resource): void {
        $maxCount = $metadata->getConstraints()[$this->getConstraintName()] ?? null;
        if ($maxCount !== null && $maxCount !== -1) {
            try {
                Validator::length(0, $maxCount)->setName($metadata->getName())->assert($metadataValues);
            } catch (NestedValidationException $e) {
                throw new RespectValidationFailedException($e);
            }
        }
    }

    /** @inheritdoc */
    public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource): void {
        throw new \BadMethodCallException('This validator can validate only the whole array of metadata');
    }
}
