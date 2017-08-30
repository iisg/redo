<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Assert\Assertion;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Respect\Validation\Validator;

class RelatedResourceKindConstraint extends AbstractMetadataConstraint {
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

    public function getSupportedControls(): array {
        return [MetadataControl::RELATIONSHIP];
    }

    public function isConfigValid($allowedResourceKindIds): bool {
        return Validator::each(
            $this->entityExistsRule->forEntityType(ResourceKind::class)
        )->validate($allowedResourceKindIds);
    }

    /** @param ResourceEntity[] $resources */
    public function isValueValid($allowedResourceKindIds, $resources): bool {
        Assertion::allInteger($allowedResourceKindIds);
        Assertion::allIsInstanceOf($resources, ResourceEntity::class);
        try {
            foreach ($resources as &$resource) {
                $resource = $this->resourceRepository->findOne($resource->getId());
            }
        } catch (EntityNotFoundException $e) {
            return false;
        }
        if (count($allowedResourceKindIds) == 0) {
            return true;
        }
        foreach ($resources as $resource) {
            if (!in_array($resource->getKind()->getId(), $allowedResourceKindIds)) {
                return false;
            }
        }
        return true;
    }
}
