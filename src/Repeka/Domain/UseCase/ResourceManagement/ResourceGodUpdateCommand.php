<?php
namespace Repeka\Domain\UseCase\ResourceManagement;

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

    public static function builder(): ResourceGodUpdateCommandBuilder {
        return new ResourceGodUpdateCommandBuilder();
    }

    public static function withParams(
        ResourceEntity $resource,
        ResourceContents $contents,
        $resourceKind,
        array $placesIds
    ): ResourceGodUpdateCommand {
        $command = new self();
        $command->resource = $resource;
        $command->contents = $contents;
        $command->resourceKind = $resourceKind;
        $command->placesIds = $placesIds;
        return $command;
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
