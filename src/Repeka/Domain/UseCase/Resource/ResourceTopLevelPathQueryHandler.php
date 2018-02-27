<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceTopLevelPathQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /** @return ResourceEntity[] */
    public function handle(ResourceTopLevelPathQuery $query): array {
        $path = [];
        $contents = $query->getResource()->getContents();
        while ($parentId = $this->parentResourceId($contents, $query->getMetadataId())) {
            $parent = $this->resourceRepository->findOne($parentId);
            $path[] = $parent;
            $contents = $parent->getContents();
        }
        return $path;
    }

    private function parentResourceId(ResourceContents $contents, int $parentMetadataId): ?int {
        return $contents->reduceAllValues(function ($value, int $metadataId, $foundParentId) use ($parentMetadataId) {
            if (!$foundParentId && $metadataId == $parentMetadataId) {
                return $value;
            }
            return $foundParentId;
        });
    }
}
