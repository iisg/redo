<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AbstractCommand;

class ResourceKindByResourceClassListQuery extends AbstractCommand {
    private $resourceClass;

    public function __construct(string $resourceClass) {
        $this->resourceClass = $resourceClass;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
