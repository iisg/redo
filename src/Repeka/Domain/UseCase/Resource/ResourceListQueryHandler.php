<?php
namespace Repeka\Domain\UseCase\Resource;

use Psr\Container\ContainerInterface;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceListQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @return ResourceEntity[]
     */
    public function handle(ResourceListQuery $query): array {
        if ($query->includeResourcesWithSystemResourceKinds()) {
            return $this->resourceRepository->findAllByResourceClass($query->getResourceClass());
        } else {
            return $this->resourceRepository->findAllNonSystemResources($query->getResourceClass());
        }
    }
}
