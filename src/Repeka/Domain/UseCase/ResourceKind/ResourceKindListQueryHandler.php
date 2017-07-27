<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindListQueryHandler {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /** @return ResourceKind[] */
    public function handle(ResourceKindListQuery $query): array {
        $resourceKinds = $this->resourceKindRepository->findAllByResourceClass($query->getResourceClass());
        if ($query->includeSystemResourceKinds()) {
            $systemResourceKinds = $this->resourceKindRepository->findAllSystemResourceKinds();
            $resourceKinds = array_merge($resourceKinds, $systemResourceKinds);
        }
        return $resourceKinds;
    }
}
