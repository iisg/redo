<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

class ResourceCloneManyTimesCommand extends ResourceClassAwareCommand {
    /** @var ResourceEntity | int */
    private $resource;
    /** @var int */
    private $cloneTimes;

    public function __construct(ResourceEntity $resource, int $cloneTimes = 1, ?User $executor = null) {
        parent::__construct($resource);
        $this->executor = $executor;
        $this->resource = $resource;
        $this->cloneTimes = $cloneTimes;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getRequiredRole(): ?SystemRole {
        return $this->resource->hasParent() ? SystemRole::OPERATOR() : SystemRole::ADMIN();
    }

    public function getCloneTimes(): int {
        return $this->cloneTimes;
    }
}
