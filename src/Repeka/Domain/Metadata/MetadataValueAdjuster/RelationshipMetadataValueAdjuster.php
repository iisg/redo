<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceEntity;

class RelationshipMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return $control == MetadataControl::RELATIONSHIP;
    }

    public function adjustMetadataValue(MetadataValue $value, Metadata $metadata): MetadataValue {
        return $value->withNewValue($this->replaceRelationshipResourceWithId($value->getValue()));
    }

    private function replaceRelationshipResourceWithId($value) {
        return $value instanceof ResourceEntity
            ? $value->getId()
            : intval($value);
    }
}
