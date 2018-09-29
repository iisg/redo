<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\FirewalledCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;

class ResourceCloneCommand extends ResourceClassAwareCommand implements AdjustableCommand, AuditedCommand, FirewalledCommand {

    /** @var ResourceKind */
    private $kind;

    /** @var ResourceContents */
    private $contents;

    /** @var ResourceEntity | int */
    private $resource;

    public function __construct(ResourceKind $resourceKind, $resource, ResourceContents $contents, ?User $executor = null) {
        parent::__construct($resourceKind);
        $this->executor = $executor;
        $this->kind = $resourceKind;
        $this->contents = $contents;
        $this->resource = $resource;
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

    public function isTopLevel(): bool {
        return empty($this->contents->getValues(SystemMetadata::PARENT));
    }
}
