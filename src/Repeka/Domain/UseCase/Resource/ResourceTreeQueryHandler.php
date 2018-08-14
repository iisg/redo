<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\TreeResult;

class ResourceTreeQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceTreeQuery $query): TreeResult {
        return $this->resourceRepository->findByTreeQuery($query);
    }
}
