<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceTransitionCommand extends Command {
    /** @var ResourceEntity */
    private $resource;
    /** @var string */
    private $transitionId;

    public function __construct(ResourceEntity $resource, string $transitionId) {
        $this->resource = $resource;
        $this->transitionId = $transitionId;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getTransitionId(): string {
        return $this->transitionId;
    }
}
