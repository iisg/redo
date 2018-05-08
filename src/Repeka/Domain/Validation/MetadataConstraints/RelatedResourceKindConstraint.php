<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Respect\Validation\Validator;

class RelatedResourceKindConstraint extends RespectValidationMetadataConstraint {
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
        return Validator::arrayType()->each(
            $this->entityExistsRule->forEntityType(ResourceKind::class)
        )->validate($allowedResourceKindIds);
    }

    public function validate(Metadata $metadata, $allowedResourceKindIds, $resourceId) {
        try {
            $resource = $this->resourceRepository->findOne($resourceId);
            Validator::in($allowedResourceKindIds)->setName($metadata->getName())->assert($resource->getKind()->getId());
        } catch (EntityNotFoundException $e) {
            // we accept relationships that are unknown; they will be possibly added or imported in the future
        }
    }
}
