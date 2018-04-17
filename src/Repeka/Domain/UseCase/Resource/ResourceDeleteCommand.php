<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

class ResourceDeleteCommand extends AbstractCommand implements AuditedCommand {
    /** @var ResourceEntity */
    private $resource;
    /** @var User */
    private $executor;

    public function __construct(ResourceEntity $resource, ?User $executor = null) {
        $this->resource = $resource;
        $this->executor = $executor;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getExecutor(): ?User {
        return $this->executor;
    }
}
