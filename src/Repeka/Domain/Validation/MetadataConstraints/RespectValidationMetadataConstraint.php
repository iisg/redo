<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

abstract class RespectValidationMetadataConstraint extends AbstractMetadataConstraint {
    /**
     * @inheritdoc
     * @param mixed $config
     * @param mixed $metadataValue
     * @return null|Validatable
     */
    protected function getValidator(Metadata $metadata, $config, $metadataValue) {
    }

    protected function validate(Metadata $metadata, $config, $metadataValue) {
        $validator = $this->getValidator($metadata, $config, $metadataValue);
        if ($validator) {
            $validator->setName($metadata->getName())->assert($metadataValue);
        }
    }

    final public function validateSingle(Metadata $metadata, $config, $metadataValue) {
        try {
            $this->validate($metadata, $config, $metadataValue);
        } catch (NestedValidationException $e) {
            throw new RespectValidationFailedException($e);
        }
    }
}
