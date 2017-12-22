<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Exception\RespectValidationFailedException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

abstract class RespectValidationMetadataConstraint extends AbstractMetadataConstraint {
    /**
     * @param mixed $config
     * @param mixed $metadataValue
     * @return null|Validatable
     */
    abstract public function getValidator($config, $metadataValue);

    final public function validateSingle($config, $metadataValue) {
        try {
            $validator = $this->getValidator($config, $metadataValue);
            if ($validator) {
                $validator->assert($metadataValue);
            }
        } catch (NestedValidationException $e) {
            throw new RespectValidationFailedException($e);
        }
    }
}
