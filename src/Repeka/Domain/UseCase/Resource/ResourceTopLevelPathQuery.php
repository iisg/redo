<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceTopLevelPathQuery extends ResourceClassAwareCommand implements NonValidatedCommand {
    use RequireNoRoles;

    /** @var ResourceEntity */
    private $resource;
    /** @var int */
    private $metadataId;

    public function __construct(ResourceEntity $resource, int $metadataId) {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->metadataId = $metadataId;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getMetadataId(): int {
        return $this->metadataId;
    }
}
