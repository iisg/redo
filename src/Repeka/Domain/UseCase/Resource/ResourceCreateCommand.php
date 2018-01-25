<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Entity\ResourceKind;

class ResourceCreateCommand extends AbstractCommand implements AdjustableCommand {
    private $kind;

    private $contents;

    public function __construct(ResourceKind $resourceKind, array $contents) {
        $this->kind = $resourceKind;
        $this->contents = $contents;
    }

    public function getKind(): ResourceKind {
        return $this->kind;
    }

    public function getContents(): array {
        return $this->contents;
    }
}
