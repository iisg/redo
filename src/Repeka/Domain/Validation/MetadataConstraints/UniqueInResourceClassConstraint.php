<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQueryBuilder;
use Respect\Validation\Validator;

class UniqueInResourceClassConstraint extends AbstractMetadataConstraint implements ConfigurableMetadataConstraint {

    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::TEXT];
    }

    public function validateAll(Metadata $metadata, array $metadataValues, ResourceEntity $resource = null) {
        if (count($metadataValues) != count(array_unique($metadataValues))) {
            throw new DomainException("Metadata {$metadata->getName()} contains duplicated values.");
        }
        parent::validateAll($metadata, $metadataValues, $resource);
    }

    public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource = null) {
        if ($this->mustBeUnique($metadata)) {
            $this->validateIsUnique(
                $metadata->getId(),
                $metadataValue,
                $metadata->getResourceClass(),
                $resource != null ? $resource->getId() : null
            );
        }
    }

    public function validateIsUnique($metadataId, $metadataValue, $resourceClass, $resourceId) {
        if ($this->valueAlreadyExists($metadataId, $metadataValue, $resourceClass, $resourceId)) {
            throw new DomainException("Metadata with value '$metadataValue' already exists");
        }
    }

    public function isConfigValid($config): bool {
        return Validator::boolType()->validate($config);
    }

    private function mustBeUnique(Metadata $metadata): bool {
        return $metadata->getConstraints()[$this->getConstraintName()] ?? false;
    }

    private function valueAlreadyExists($metadataId, $metadataValue, $resourceClass, $resourceId): bool {
        $builder = new ResourceListQueryBuilder();
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
        return sizeof($matchingResources) > 0;
    }
}
