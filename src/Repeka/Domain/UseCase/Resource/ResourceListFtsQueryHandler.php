<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Repository\ResourceFtsProvider;

class ResourceListFtsQueryHandler {
    /** @var ResourceFtsProvider */
    private $resourceFtsProvider;

    public function __construct(ResourceFtsProvider $resourceFtsProvider) {
        $this->resourceFtsProvider = $resourceFtsProvider;
    }

    public function handle(ResourceListFtsQuery $query) {
        return $this->resourceFtsProvider->search($query);
    }
}
