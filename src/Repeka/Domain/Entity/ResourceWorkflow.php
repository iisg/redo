<?php
namespace Repeka\Domain\Entity;

interface ResourceWorkflow {
    public function getCurrentMarking(ResourceEntity $resource): string;

    /** @return string[] */
    public function getEnabledTransitions(ResourceEntity $resource): array;

    public function apply(ResourceEntity $resource, string $transition): ResourceEntity;
}
