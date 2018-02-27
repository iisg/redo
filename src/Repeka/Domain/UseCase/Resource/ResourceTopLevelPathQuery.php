<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceTopLevelPathQuery extends AbstractCommand implements NonValidatedCommand {
    /** @var ResourceEntity */
    private $resource;
    /** @var int */
    private $metadataId;

    public function __construct(ResourceEntity $resource, int $metadataId) {
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
