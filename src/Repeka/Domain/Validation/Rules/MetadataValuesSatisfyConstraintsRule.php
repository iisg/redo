<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Respect\Validation\Rules\AbstractRule;

class MetadataValuesSatisfyConstraintsRule extends AbstractRule {
    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceWorkflowTransition */
    private $transition;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataConstraintManager $metadataConstraintManager, MetadataRepository $metadataRepository) {
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->metadataRepository = $metadataRepository;
    }

    public function forResourceAndTransition(ResourceEntity $resource, ?ResourceWorkflowTransition $transition): self {
        $instance = new self($this->metadataConstraintManager, $this->metadataRepository);
        $instance->resource = $resource;
        $instance->transition = $transition;
        return $instance;
    }

    public function forResource(ResourceEntity $resource): self {
        return $this->forResourceAndTransition($resource, null);
    }

    /** @param ResourceContents $contents */
    public function validate($contents) {
        Assertion::notNull(
            $this->resource,
            'Resource not set. Use forResourceAndTransition() to create validator for specific resource first.'
        );
        Assertion::isInstanceOf($contents, ResourceContents::class);
        $resourceKind = $this->resource->getKind();
        $lockedMetadataIds = [];
        if ($this->transition && $this->resource->hasWorkflow()) {
            foreach ($this->transition->getToIds() as $targetPlaceId) {
                $targetPlace = $this->resource->getWorkflow()->getPlace($targetPlaceId);
                $lockedMetadataIdsInPlace = $targetPlace->restrictingMetadataIds()->locked()->assignees()->autoAssign()->get();
                $lockedMetadataIds = array_merge($lockedMetadataIds, $lockedMetadataIdsInPlace);
            }
        }
        $lockedMetadataIds = array_values(array_unique($lockedMetadataIds));
        $contents->forEachMetadata(
            function (array $values, int $metadataId) use ($lockedMetadataIds, $resourceKind) {
                if (!in_array($metadataId, $lockedMetadataIds)) {
                    $metadataKind = $resourceKind->hasMetadata($metadataId)
                        ? $resourceKind->getMetadataById($metadataId)
                        : $this->metadataRepository->findOne($metadataId);
                    if (!$metadataKind->isDynamic()) {
                        $constraints = $this->metadataConstraintManager->getSupportedConstraintNamesForControl($metadataKind->getControl());
                        foreach ($constraints as $constraintName) {
                            $constraint = $this->metadataConstraintManager->get($constraintName);
                            $constraint->validateAll($metadataKind, $values, $this->resource);
                        }
                    }
                }
            }
        );
        return true;
    }
}
