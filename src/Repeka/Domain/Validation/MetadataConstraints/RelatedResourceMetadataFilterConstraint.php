<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Respect\Validation\Validator;

class RelatedResourceMetadataFilterConstraint extends RespectValidationMetadataConstraint {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceContentsCorrectStructureRule */
    private $structureRule;

    public function __construct(ResourceRepository $resourceRepository, ResourceContentsCorrectStructureRule $structureRule) {
        $this->resourceRepository = $resourceRepository;
        $this->structureRule = $structureRule;
    }

    public function getConstraintName(): string {
        return 'relatedResourceMetadataFilter';
    }

    public function getSupportedControls(): array {
        return [MetadataControl::RELATIONSHIP];
    }

    public function isConfigValid($contentsFilter): bool {
        return Validator::allOf(Validator::arrayType(), $this->structureRule)
            ->validate(ResourceContents::fromArray($contentsFilter)->toArray());
    }

    public function getValidator($contentsFilter, $resource) {
        $query = ResourceListQuery::builder()
            ->filterByIds([$resource->getId()])
            ->filterByContents(ResourceContents::fromArray($contentsFilter))
            ->build();
        $matchedResources = $this->resourceRepository->findByQuery($query);
        $valid = count($matchedResources) == 1;
        if (!$valid) {
            return Validator::alwaysInvalid();
        }
    }
}
