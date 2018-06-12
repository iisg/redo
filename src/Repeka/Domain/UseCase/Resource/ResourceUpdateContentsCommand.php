<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

class ResourceUpdateContentsCommand extends ResourceClassAwareCommand implements AuditedCommand {
    use RequireOperatorRole;

    private $resource;
    private $contents;

    public function __construct(ResourceEntity $resource, ResourceContents $contents, ?User $executor = null) {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->contents = $contents;
        $this->executor = $executor;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getContents(): ResourceContents {
        return $this->contents;
    }
}
