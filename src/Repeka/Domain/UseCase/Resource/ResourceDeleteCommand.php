<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceDeleteCommand extends ResourceClassAwareCommand implements AuditedCommand {
    /** @var ResourceEntity */
    private $resource;

    public function __construct(ResourceEntity $resource) {
        parent::__construct($resource);
        $this->resource = $resource;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }
}
