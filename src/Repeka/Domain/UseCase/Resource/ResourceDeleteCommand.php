<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceDeleteCommand extends AbstractCommand implements AuditedCommand {
    /** @var ResourceEntity */
    private $resource;

    public function __construct(ResourceEntity $resource) {
        $this->resource = $resource;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }
}
