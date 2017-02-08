<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\ResourceEntity;

interface ResourceWorkflowStrategy {
    /** @return string[] */
    public function getPlaces(ResourceEntity $resource): array;

    /** @return string[] */
    public function getTransitions(ResourceEntity $resource): array;

    public function apply(ResourceEntity $resource, string $transition): ResourceEntity;

    public function setCurrentPlaces(ResourceEntity $resourceEntity, array $places): ResourceEntity;
}
