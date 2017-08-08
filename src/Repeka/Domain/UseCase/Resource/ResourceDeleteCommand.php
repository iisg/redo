<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceDeleteCommand extends Command {
    /** @var ResourceEntity */
    private $resource;

    public function __construct(ResourceEntity $resource) {
        $this->resource = $resource;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }
}
