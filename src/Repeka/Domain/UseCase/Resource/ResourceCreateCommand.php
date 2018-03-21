<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;

class ResourceCreateCommand extends AbstractCommand implements AuditedCommand {
    private $kind;

    private $contents;

    public function __construct(ResourceKind $resourceKind, ResourceContents $contents) {
        $this->kind = $resourceKind;
        $this->contents = $contents;
    }

    public function getKind(): ResourceKind {
        return $this->kind;
    }

    public function getContents(): ResourceContents {
        return $this->contents;
    }
}
