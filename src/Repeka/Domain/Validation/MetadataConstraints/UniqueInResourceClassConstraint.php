<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

class UniqueInResourceClassConstraint extends MetadataConstraintWithoutConfiguration {
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::TEXT];
    }

    public function doValidateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource = null): void {
        $this->validateIsUnique(
            $metadata->getId(),
            $metadataValue,
            $metadata->getResourceClass(),
            $resource != null ? $resource->getId() : null
        );
        $valuesInCurrentResource = $resource->getValuesWithoutSubmetadata($metadata);
        if ((array_count_values($valuesInCurrentResource)[$metadataValue] ?? 1) !== 1) {
            throw new DomainException("The value '$metadataValue' is repeated.");
        }
    }

    public function validateIsUnique($metadataId, $metadataValue, $resourceClass, $resourceId) {
        if ($this->valueAlreadyExists($metadataId, $metadataValue, $resourceClass, $resourceId)) {
            throw new DomainException("Metadata with value '$metadataValue' already exists");
        }
    }

    private function valueAlreadyExists($metadataId, $metadataValue, $resourceClass, $resourceId): bool {
        $builder = ResourceListQuery::builder();
        $query = $builder
            ->filterByResourceClass($resourceClass)
            ->filterByContents([$metadataId => '^' . $metadataValue . '$'])
            ->build();
        $results = $this->resourceRepository->findByQuery($query)->getResults();
        $matchingResources = array_filter(
            $results,
            function (ResourceEntity $matchedResource) use ($resourceId) {
                return $matchedResource->getId() !== $resourceId;
            }
        );
        return count($matchingResources) > 0;
    }
}
