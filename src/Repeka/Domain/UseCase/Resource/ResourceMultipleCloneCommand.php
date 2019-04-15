<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\FirewalledCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;

class ResourceMultipleCloneCommand extends ResourceClassAwareCommand implements AdjustableCommand, FirewalledCommand {

    /** @var ResourceKind */
    private $kind;

    /** @var ResourceContents */
    private $contents;

    /** @var ResourceEntity | int */
    private $resource;
    /** @var int */
    private $cloneTimes;

    public function __construct(
        ResourceKind $resourceKind,
        $resource,
        ResourceContents $contents,
        int $cloneTimes = 1,
        ?User $executor = null
    ) {
        parent::__construct($resourceKind);
        $this->executor = $executor;
        $this->kind = $resourceKind;
        $this->contents = $contents;
        $this->resource = $resource;
        $this->cloneTimes = $cloneTimes;
    }

    public function getKind(): ResourceKind {
        return $this->kind;
    }

    public function getContents(): ResourceContents {
        return $this->contents;
    }

    public function getResource() {
        return $this->resource;
    }

    public function getRequiredRole(): ?SystemRole {
        return $this->isTopLevel() ? SystemRole::ADMIN() : SystemRole::OPERATOR();
    }

    public function getCloneTimes(): int {
        return $this->cloneTimes;
    }

    public function isTopLevel(): bool {
        return empty($this->contents->getValues(SystemMetadata::PARENT));
    }
}
