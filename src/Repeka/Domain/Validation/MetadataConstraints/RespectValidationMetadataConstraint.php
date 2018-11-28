<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Respect\Validation\Exceptions\NestedValidationException;

abstract class RespectValidationMetadataConstraint extends AbstractMetadataConstraint {
    /** @inheritdoc */
    protected function getValidator(Metadata $metadata, $metadataValue) {
    }

    protected function validate(Metadata $metadata, $metadataValue) {
        $validator = $this->getValidator($metadata, $metadataValue);
        if ($validator) {
            $validator->setName($metadata->getName())->assert($metadataValue);
        }
    }

    final public function validateSingle(Metadata $metadata, $metadataValue) {
        try {
            $this->validate($metadata, $metadataValue);
        } catch (NestedValidationException $e) {
            throw new RespectValidationFailedException($e);
        }
    }
}
