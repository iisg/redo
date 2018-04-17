<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;

class ResourceCreateCommand extends AbstractCommand implements AuditedCommand {
    private $kind;
    private $contents;
    /** @var null|User */
    private $executor;

    public function __construct(ResourceKind $resourceKind, ResourceContents $contents, ?User $executor = null) {
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

    public function getExecutor(): ?User {
        return $this->executor;
    }
}
