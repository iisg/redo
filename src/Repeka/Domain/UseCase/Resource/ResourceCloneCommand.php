<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\FirewalledCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

class ResourceCloneCommand extends ResourceClassAwareCommand implements NonValidatedCommand, AuditedCommand, FirewalledCommand {
    /** @var ResourceEntity | int */
    private $resource;

    public function __construct(ResourceEntity $resource, ?User $executor = null) {
        parent::__construct($resource);
        $this->executor = $executor;
        $this->resource = $resource;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getRequiredRole(): ?SystemRole {
        return $this->isTopLevel() ? SystemRole::ADMIN() : SystemRole::OPERATOR();
    }

    public function isTopLevel(): bool {
        return !$this->resource->hasParent();
    }
}
