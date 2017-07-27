<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;

class MetadataListByResourceClassQuery extends Command {
    private $resourceClass;

    public function __construct(string $resourceClass) {
        $this->resourceClass = $resourceClass;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
