<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceUpdateContentsCommand extends AbstractCommand {
    private $resource;
    private $contents;

    public function __construct(ResourceEntity $resource, ResourceContents $contents) {
        $this->resource = $resource;
        $this->contents = $contents;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getContents(): ResourceContents {
        return $this->contents;
    }
}
