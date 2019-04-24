<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Respect\Validation\Validator;

abstract class MetadataConstraintWithoutConfiguration extends AbstractMetadataConstraint implements ConfigurableMetadataConstraint {
    final public function isConfigValid($config): bool {
        return Validator::boolType()->validate($config);
    }

    final public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource): void {
        if ($this->isEnabled($metadata)) {
            $this->doValidateSingle($metadata, $metadataValue, $resource);
        }
    }

    protected function isEnabled(Metadata $metadata): bool {
        return $metadata->getConstraints()[$this->getConstraintName()] ?? false;
    }

    abstract protected function doValidateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource): void;
}
