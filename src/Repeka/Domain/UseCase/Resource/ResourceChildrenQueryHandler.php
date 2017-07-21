<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Repository\ResourceRepository;

class ResourceChildrenQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceChildrenQuery $query): array {
        return ($query->getId() === null)
            ? $this->resourceRepository->findTopLevel()
            : $this->resourceRepository->findChildren($query->getId());
    }
}
