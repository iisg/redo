<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;

class MetadataListByResourceClassQuery extends AbstractCommand {
    private $resourceClass;

    public function __construct(string $resourceClass) {
        $this->resourceClass = $resourceClass;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
