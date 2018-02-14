<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;

class ResourceListQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceListQuery $query): PageResult {
        return $this->resourceRepository->findByQuery($query);
    }
}
