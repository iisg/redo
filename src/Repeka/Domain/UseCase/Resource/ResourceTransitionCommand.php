<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Workflow\ResourceWorkflow;
use Repeka\Domain\Workflow\ResourceWorkflowRepository;

class ResourceTransitionCommand extends Command {
    /** @var ResourceEntity */
    private $resource;
    /** @var string */
    private $transition;

    public function __construct(ResourceEntity $resource, string $transition) {
        $this->resource = $resource;
        $this->transition = $transition;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getTransition(): string {
        return $this->transition;
    }
}
