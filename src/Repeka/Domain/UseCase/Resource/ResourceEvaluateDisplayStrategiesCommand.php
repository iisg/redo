<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceEvaluateDisplayStrategiesCommand extends ResourceClassAwareCommand implements NonValidatedCommand {
    use RequireOperatorRole;

    /** @var ResourceEntity */
    private $resource;

    public function __construct(ResourceEntity $resource) {
        parent::__construct($resource);
        $this->resource = $resource;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }
}
