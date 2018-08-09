<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceEvaluateDisplayStrategiesCommand extends AbstractCommand implements NonValidatedCommand {
    use RequireOperatorRole;

    /** @var ResourceEntity */
    private $resource;

    public function __construct(ResourceEntity $resource) {
        $this->resource = $resource;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }
}
