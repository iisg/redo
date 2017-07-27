<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class ResourceKindListQuery extends Command {
    private $includeSystemResourceKinds;
    private $resourceClass;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(string $resourceClass, bool $includeSystemResourceKinds = true) {
        $this->includeSystemResourceKinds = $includeSystemResourceKinds;
        $this->resourceClass = $resourceClass;
    }

    public function includeSystemResourceKinds(): bool {
        return $this->includeSystemResourceKinds;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
