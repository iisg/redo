<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;

class ResourceListQuery extends Command {
    private $includeResourcesWithSystemResourceKinds;
    private $resourceClass;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(string $resourceClass, bool $withSystemResourceKinds = false) {
        $this->includeResourcesWithSystemResourceKinds = $withSystemResourceKinds;
        $this->resourceClass = $resourceClass;
    }

    public function includeResourcesWithSystemResourceKinds(): bool {
        return $this->includeResourcesWithSystemResourceKinds;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
