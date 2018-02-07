<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\ResourceKind;

class ResourceKindDeleteCommand extends AbstractCommand {
    /** @var ResourceKind */
    private $resourceKind;

    public function __construct(ResourceKind $resourceKind) {
        $this->resourceKind = $resourceKind;
    }

    public function getResourceKind(): ResourceKind {
        return $this->resourceKind;
    }
}
