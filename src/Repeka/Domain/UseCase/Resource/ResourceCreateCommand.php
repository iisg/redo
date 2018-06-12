<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;

class ResourceCreateCommand extends ResourceClassAwareCommand implements AuditedCommand {
    private $kind;
    private $contents;

    public function __construct(ResourceKind $resourceKind, ResourceContents $contents, ?User $executor = null) {
        parent::__construct($resourceKind);
        $this->kind = $resourceKind;
        $this->contents = $contents;
        $this->executor = $executor;
    }

    public function getKind(): ResourceKind {
        return $this->kind;
    }

    public function getContents(): ResourceContents {
        return $this->contents;
    }

    public function getRequiredRole(): ?SystemRole {
        return $this->contents->getValues(SystemMetadata::PARENT) ? SystemRole::OPERATOR() : parent::getRequiredRole();
    }
}
