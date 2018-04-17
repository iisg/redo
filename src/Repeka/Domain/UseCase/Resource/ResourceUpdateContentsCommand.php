<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

class ResourceUpdateContentsCommand extends AbstractCommand implements AuditedCommand {
    private $resource;
    private $contents;
    /** @var User */
    private $executor;

    public function __construct(ResourceEntity $resource, ResourceContents $contents, ?User $executor = null) {
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

    public function getExecutor(): ?User {
        return $this->executor;
    }
}
