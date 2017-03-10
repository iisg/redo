<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Respect\Validation\Validator;

class ResourceHasAllowedKindConstraint extends AbstractMetadataConstraint {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var EntityExistsRule */
    private $entityExistsRule;

    public function __construct(ResourceRepository $resourceRepository, EntityExistsRule $entityExistsRule) {
        $this->resourceRepository = $resourceRepository;
        $this->entityExistsRule = $entityExistsRule;
    }

    public function getConstraintName(): string {
        return 'resourceKind';
    }

    public function validateArgument($allowedResourceKindIds): bool {
        return Validator::each(
            $this->entityExistsRule->forEntityType(ResourceKind::class)
        )->validate($allowedResourceKindIds);
    }

    /** @param ResourceEntity $resource */
    public function validateValue($allowedResourceKindIds, $resource): bool {
        Assertion::allInteger($allowedResourceKindIds);
        Assertion::isInstanceOf($resource, ResourceEntity::class);
        try {
            $resource = $this->resourceRepository->findOne($resource->getId());
        } catch (EntityNotFoundException $e) {
            return false;
        }
        return (count($allowedResourceKindIds) == 0) || in_array($resource->getKind()->getId(), $allowedResourceKindIds);
    }
}
