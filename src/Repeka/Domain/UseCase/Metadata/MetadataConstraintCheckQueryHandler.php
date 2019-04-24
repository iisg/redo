<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;

class MetadataConstraintCheckQueryHandler {
    public function handle(MetadataConstraintCheckQuery $query) {
        $constraint = $query->getConstraint();
        $resource = $query->getResource();
        if (!$resource) {
            $resource = new ResourceEntity($query->getResourceKind(), ResourceContents::empty());
        }
        $originalContents = $resource->getContents();
        $resource->updateContents($query->getCurrentContents());
        try {
            $constraint->validateSingle($query->getMetadata(), $query->getValue(), $resource);
        } finally {
            $resource->updateContents($originalContents);
        }
        return true;
    }
}
