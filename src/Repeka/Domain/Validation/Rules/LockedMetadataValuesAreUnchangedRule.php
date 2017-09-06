<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Respect\Validation\Rules\AbstractRule;

class LockedMetadataValuesAreUnchangedRule extends AbstractRule {
    /** @var ResourceEntity */
    private $resource;

    public function forResource(ResourceEntity $resource): LockedMetadataValuesAreUnchangedRule {
        $instance = new self();
        $instance->resource = $resource;
        return $instance;
    }

    public function validate($input) {
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
        $lockedMetadataIds = [];
        foreach ($currentPlaces as $currentPlace) {
            $lockedMetadataIds = array_merge($lockedMetadataIds, $currentPlace->getLockedMetadataIds());
        }
        $lockedMetadataIds = array_intersect(
            array_keys($currentContents),
            array_unique($lockedMetadataIds)
        );
        $modifiedLockedMetadata = array_values(array_filter(
            $lockedMetadataIds,
            function ($baseId) use ($currentContents, $input) {
                return $currentContents[$baseId] != $input[$baseId];
            }
        ));
        if (count($modifiedLockedMetadata) > 0) {
            sort($modifiedLockedMetadata);
            $nameForErrorMessage = implode(', ', $modifiedLockedMetadata);
            $this->setName("Metadata $nameForErrorMessage");
            return false;
        }
        return true;
    }
}
