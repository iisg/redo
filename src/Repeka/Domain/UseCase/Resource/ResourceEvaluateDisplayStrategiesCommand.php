<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceEvaluateDisplayStrategiesCommand extends AbstractCommand implements NonValidatedCommand {
    use RequireNoRoles;

    /** @var ResourceEntity */
    private $resource;
    private $metadataIds;

    public function __construct(ResourceEntity $resource, array $metadataIds = []) {
        $this->resource = $resource;
        $this->metadataIds = $metadataIds;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getMetadataIds(): array {
        return $this->metadataIds;
    }
}
