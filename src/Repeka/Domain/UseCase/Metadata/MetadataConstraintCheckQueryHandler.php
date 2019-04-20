<?php
namespace Repeka\Domain\UseCase\Metadata;

class MetadataConstraintCheckQueryHandler {
    public function handle(MetadataConstraintCheckQuery $query) {
        $constraint = $query->getConstraint();
        $resource = $query->getResource();
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
