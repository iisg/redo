<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\ResourceEntity;

interface ResourceWorkflowDriver {
    /** @return string[] */
    public function getPlaces(ResourceEntity $resource): array;

    /** @return string[] */
    public function getTransitions(ResourceEntity $resource): array;

    public function apply(ResourceEntity $resource, string $transitionId): ResourceEntity;

    public function setCurrentPlaces(ResourceEntity $resourceEntity, array $places): ResourceEntity;
}
