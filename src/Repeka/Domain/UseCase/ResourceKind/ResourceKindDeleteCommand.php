<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceKind;

class ResourceKindDeleteCommand extends ResourceClassAwareCommand {
    /** @var ResourceKind */
    private $resourceKind;

    public function __construct(ResourceKind $resourceKind) {
        parent::__construct($resourceKind);
        $this->resourceKind = $resourceKind;
    }

    public function getResourceKind(): ResourceKind {
        return $this->resourceKind;
    }
}
