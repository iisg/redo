<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceUpdateContentsCommand extends Command {
    private $resource;

    private $contents;

    public function __construct(ResourceEntity $resource, array $contents) {
        $this->resource = $resource;
        $this->contents = $contents;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getContents(): array {
        return $this->contents;
    }
}
