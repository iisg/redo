<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Utils\EntityUtils;
use Respect\Validation\Rules\AbstractRule;

class LockedMetadataValuesAreUnchangedRule extends AbstractRule {
    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceWorkflowTransition */
    private $transition;

    public function forResourceAndTransition(ResourceEntity $resource, ResourceWorkflowTransition $transition = null): self {
        $instance = new self();
        $instance->resource = $resource;
        $instance->transition = $transition;
        return $instance;
    }

    public function validate($newContents) {
        Assertion::notNull(
            $this->resource,
            'Resource not set. Use forResource() to create validator for specific resource first.'
        );
        /** @var ResourceWorkflow $workflow */
        $workflow = $this->resource->getWorkflow();
        if ($workflow === null) {
            return true;
        }
        Assertion::notNull(
            $this->transition,
            'Transition not set. Use forTransition() to create validator for specific transition first.'
        );
        $toIds = $this->transition->getToIds();
        /** @var ResourceWorkflowPlace[] $nextPlaces */
        $nextPlaces = EntityUtils::getByIds($toIds, $workflow->getPlaces());
        $currentContents = $this->resource->getContents();
        $lockedMetadataIds = $this->getLockedMetadataIds($currentContents, $nextPlaces);
        $modifiedLockedMetadataIds = $this->getModifiedLockedMetadataIds($currentContents, $newContents, $lockedMetadataIds);
        if (count($modifiedLockedMetadataIds) > 0) {
            sort($modifiedLockedMetadataIds);
            $nameForErrorMessage = implode(', ', $modifiedLockedMetadataIds);
            $this->setName("Metadata $nameForErrorMessage");
            return false;
        }
        return true;
    }

    /**
     * @param ResourceWorkflowPlace[] $targetPlaces
     * @return int[]
     */
    private function getLockedMetadataIds(ResourceContents $currentContents, array $targetPlaces): array {
        $lockedIds = [];
        foreach ($targetPlaces as $targetPlace) {
            $lockedIds = array_merge($lockedIds, $targetPlace->restrictingMetadataIds()->locked()->assignees()->autoAssign()->get());
        }
        $lockedIds = array_intersect(
            array_keys($currentContents->toArray()),
            array_unique($lockedIds)
        );
        return $lockedIds;
    }

    /**
     * @param int[] $lockedMetadataIds
     * @return int[]
     */
    private function getModifiedLockedMetadataIds(
        ResourceContents $currentContents,
        ResourceContents $newContents,
        array $lockedMetadataIds
    ): array {
        $newContents = $this->replaceObjectsWithIds($newContents)->filterOutEmptyMetadata();
        $modifiedIds = [];
        foreach ($lockedMetadataIds as $id) {
            if ($currentContents[$id] != $newContents[$id]) {
                $modifiedIds[] = $id;
            }
        }
        return $modifiedIds;
    }

    private function replaceObjectsWithIds(ResourceContents $contents): ResourceContents {
        return $contents->mapAllValues(
            function ($value) {
                if ($value instanceof Identifiable) {
                    return $value->getId();
                } else {
                    return $value;
                }
            }
        );
    }
}
