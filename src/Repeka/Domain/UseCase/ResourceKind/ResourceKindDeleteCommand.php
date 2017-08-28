<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;

class ResourceKindDeleteCommand extends Command {
    /** @var ResourceKind */
    private $resourceKind;

    public function __construct(ResourceKind $resourceKind) {
        $this->resourceKind = $resourceKind;
    }

    public function getResourceKind(): ResourceKind {
        return $this->resourceKind;
    }
}
