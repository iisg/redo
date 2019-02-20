<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;

interface ResourceWorkflowDriver {
    /** @return string[] */
    public function getPlaces(ResourceEntity $resource): array;

    /** @return string[] */
    public function getTransitions(ResourceEntity $resource): array;

    public function apply(ResourceEntity $resource, string $transitionId): ResourceEntity;

    public function setCurrentPlaces(ResourceEntity $resourceEntity, array $places): ResourceEntity;

    /** @return string[] */
    public function getTransitionsFromPlace(ResourceWorkflowPlace $place): array;
}
