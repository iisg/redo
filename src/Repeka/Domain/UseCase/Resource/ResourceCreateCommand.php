<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;

class ResourceCreateCommand extends Command {
    private $kind;

    private $contents;

    private $resourceClass;

    public function __construct(ResourceKind $resourceKind, array $contents, string $resourceClass) {
        $this->kind = $resourceKind;
        $this->contents = $contents;
        $this->resourceClass = $resourceClass;
    }

    public function getKind(): ResourceKind {
        return $this->kind;
    }

    public function getContents(): array {
        return $this->contents;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
