<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindByResourceClassListQueryHandler {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /** @return ResourceKind[] */
    public function handle(ResourceKindByResourceClassListQuery $query): array {
        return $this->resourceKindRepository->findAllByResourceClass($query->getResourceClass());
    }
}
