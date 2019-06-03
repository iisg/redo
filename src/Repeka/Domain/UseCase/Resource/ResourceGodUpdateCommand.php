<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;

class ResourceGodUpdateCommand extends AbstractCommand implements AuditedCommand, AdjustableCommand, HasResourceClass, NonValidatedCommand {
    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceContents */
    private $contents;
    /** @var ResourceKind | int */
    private $resourceKind;
    /** @var array */
    private $placesIds;

    public static function builder(?ResourceEntity $resource = null): ResourceGodUpdateCommandBuilder {
        return new ResourceGodUpdateCommandBuilder($resource);
    }

    public function __construct(ResourceEntity $resource, ?ResourceContents $contents = null, $resourceKind = null, array $placesIds = []) {
        $this->resource = $resource;
        $this->contents = $contents;
        $this->resourceKind = $resourceKind;
        $this->placesIds = $placesIds;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getContents(): ResourceContents {
        return $this->contents;
    }

    /** @return ResourceKind | int | null */
    public function getResourceKind() {
        return $this->resourceKind;
    }

    /** @return string[] */
    public function getPlacesIds(): array {
        return $this->placesIds;
    }

    public function getResourceClass(): string {
        return $this->resource->getResourceClass();
    }
}
