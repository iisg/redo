<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Respect\Validation\Rules\AbstractRule;

class LockedMetadataValuesAreUnchangedRule extends AbstractRule {
    /** @var ResourceEntity */
    private $resource;

    public function forResource(ResourceEntity $resource): LockedMetadataValuesAreUnchangedRule {
        $instance = new self();
        $instance->resource = $resource;
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
        /** @var ResourceWorkflowPlace[] $currentPlaces */
        $currentPlaces = $workflow->getPlaces($this->resource);
        $currentContents = $this->resource->getContents();
        $lockedMetadataIds = $this->getLockedMetadataIds($currentContents, $currentPlaces);
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
     * @param ResourceWorkflowPlace[] $currentContents
     * @return int[]
     */
    private function getLockedMetadataIds(array $currentContents, array $currentPlaces): array {
        $lockedIds = [];
        foreach ($currentPlaces as $currentPlace) {
            $lockedIds = array_merge($lockedIds, $currentPlace->restrictingMetadataIds()->locked()->assignees()->get());
        }
        $lockedIds = array_intersect(
            array_keys($currentContents),
            array_unique($lockedIds)
        );
        return $lockedIds;
    }

    /**
     * @param int[] $lockedMetadataIds
     * @return int[]
     */
    private function getModifiedLockedMetadataIds(array $currentContents, array $newContents, array $lockedMetadataIds): array {
        $this->replaceObjectsWithIds($newContents);
        $modifiedIds = [];
        foreach ($lockedMetadataIds as $id) {
            if ($currentContents[$id] != $newContents[$id]) {
                $modifiedIds[] = $id;
            }
        }
        return $modifiedIds;
    }

    private function replaceObjectsWithIds(array &$contents) {
        foreach ($contents as &$values) {
            $values = array_map(function (array $metadataValue) {
                /** @var Identifiable|mixed $item */
                if (is_object($metadataValue['value'])) {
                    $metadataValue['value'] = $metadataValue['value']->getId();
                }
                if (isset($metadataValue['submetadata'])) {
                    $this->replaceObjectsWithIds($metadataValue['submetadata']);
                }
                return $metadataValue;
            }, $values);
        }
    }
}
