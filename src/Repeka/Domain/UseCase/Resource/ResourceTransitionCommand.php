<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;

class ResourceTransitionCommand extends ResourceClassAwareCommand implements AuditedCommand, AdjustableCommand {
    use RequireOperatorRole;

    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceWorkflowTransition | string */
    private $transitionOrId;
    /** @var ResourceContents */
    private $contents;

    public function __construct(ResourceEntity $resource, ResourceContents $contents, $transitionOrId, ?User $executor = null) {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->transitionOrId = $transitionOrId;
        $this->executor = $executor;
        $this->contents = $contents;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    /** @return ResourceWorkflowTransition */
    public function getTransition() {
        return $this->transitionOrId;
    }

    public function getContents(): ResourceContents {
        return $this->contents;
    }
}
