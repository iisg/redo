<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;

class MetadataTopLevelPathQueryHandler {
    /** @return Metadata[] */
    public function handle(MetadataTopLevelPathQuery $query): array {
        $metadata = $query->getMetadata();
        $path = [];
        while (!$metadata->isTopLevel()) {
            $metadata = $metadata->getParent();
            $path[] = $metadata;
        }
        return $path;
    }
}
