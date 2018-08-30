<?php
namespace Repeka\Domain\UseCase\Resource;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;

class ResourceGodUpdateCommandBuilder {
    private $resource;
    private $contents = [];
    private $resourceKind;
    private $placesIds = [];

    public function setResource(ResourceEntity $resource): ResourceGodUpdateCommandBuilder {
        $this->resource = $resource;
        return $this;
    }

    /** @param ResourceContents|array $contents */
    public function setNewContents($contents): ResourceGodUpdateCommandBuilder {
        $this->contents = $contents;
        return $this;
    }

    /** @param ResourceKind | int $resourceKinds */
    public function changeResourceKind($resourceKind): ResourceGodUpdateCommandBuilder {
        $this->resourceKind = $resourceKind;
        return $this;
    }

    /** @param string[] $placesIds */
    public function changePlaces(array $placesIds): ResourceGodUpdateCommandBuilder {
        $this->placesIds = array_values(array_unique(array_merge($this->placesIds, $placesIds)));
        return $this;
    }

    public function build(): ResourceGodUpdateCommand {
        Assertion::notNull($this->resource, 'You must set the resource.');
        return ResourceGodUpdateCommand::withParams(
            $this->resource,
            $this->contents instanceof ResourceContents ? $this->contents : ResourceContents::fromArray($this->contents),
            $this->resourceKind,
            $this->placesIds
        );
    }
}
