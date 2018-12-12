<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Respect\Validation\Rules\AbstractRule;

class MetadataValuesSatisfyConstraintsRule extends AbstractRule {
    /** @var ResourceEntity */
    private $resource;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataConstraintManager $metadataConstraintManager, MetadataRepository $metadataRepository) {
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->metadataRepository = $metadataRepository;
    }

    public function forResource(ResourceEntity $resource): MetadataValuesSatisfyConstraintsRule {
        $instance = new self($this->metadataConstraintManager, $this->metadataRepository);
        $instance->resource = $resource;
        return $instance;
    }

    /** @param ResourceContents $contents */
    public function validate($contents) {
        Assertion::notNull(
            $this->resource,
            'Resource not set. Use forResource() to create validator for specific resource first.'
        );
        Assertion::isInstanceOf($contents, ResourceContents::class);
        $resourceKind = $this->resource->getKind();
        $contents->forEachMetadata(
            function (array $values, int $metadataId) use ($resourceKind) {
                $metadataKind = $resourceKind->hasMetadata($metadataId)
                    ? $resourceKind->getMetadataById($metadataId)
                    : $this->metadataRepository->findOne($metadataId);
                $constraints = $this->metadataConstraintManager->getSupportedConstraintNamesForControl($metadataKind->getControl());
                foreach ($constraints as $constraintName) {
                    $constraint = $this->metadataConstraintManager->get($constraintName);
                    $constraint->validateAll($metadataKind, $values, $this->resource);
                }
            }
        );
        return true;
    }
}
