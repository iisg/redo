<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

class ResourceTransitionCommand extends AbstractCommand implements AuditedCommand {
    /** @var ResourceEntity */
    private $resource;
    /** @var string */
    private $transitionId;
    /** @var User */
    private $executor;

    public function __construct(ResourceEntity $resource, string $transitionId, ?User $executor = null) {
        $this->resource = $resource;
        $this->transitionId = $transitionId;
        $this->executor = $executor;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getTransitionId(): string {
        return $this->transitionId;
    }

    public function getExecutor(): ?User {
        return $this->executor;
    }
}
