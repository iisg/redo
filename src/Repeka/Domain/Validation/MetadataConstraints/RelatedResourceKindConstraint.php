<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
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

    public function getValidator($allowedResourceKindIds, $resource) {
        $resource = $this->resourceRepository->findOne($resource->getId());
        $valid = Validator::in($allowedResourceKindIds)->validate($resource->getKind()->getId());
        if (!$valid) {
            return Validator::alwaysInvalid();
        }
    }
}
