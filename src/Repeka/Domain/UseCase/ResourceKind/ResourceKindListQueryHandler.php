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
        return $this->getResults($query);
    }

    private function getResults(ResourceKindListQuery $query):array {
        if ($query->includeSystemResourceKinds()) {
            return $this->resourceKindRepository->findAll();
        } else {
            return $this->resourceKindRepository->findAllNonSystemResourceKinds();
        }
    }
}
