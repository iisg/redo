<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Respect\Validation\Rules\AbstractRule;

class MetadataValuesSatisfyConstraintsRule extends AbstractRule {
    /** @var ResourceKind */
    private $resourceKind;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataConstraintManager $metadataConstraintManager, MetadataRepository $metadataRepository) {
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->metadataRepository = $metadataRepository;
    }

    public function forResourceKind($resourceKind): MetadataValuesSatisfyConstraintsRule {
        $instance = new self($this->metadataConstraintManager, $this->metadataRepository);
        $instance->resourceKind = $resourceKind;
        return $instance;
    }

    /** @param ResourceContents $contents */
    public function validate($contents) {
        Assertion::notNull(
            $this->resourceKind,
            'Resource kind not set. Use forResourceKind() to create validator for specific resource kind first.'
        );
        Assertion::isInstanceOf($contents, ResourceContents::class);
        $contents->forEachMetadata(function (array $values, int $metadataId) {
            $metadataKind = $this->resourceKind->hasMetadata($metadataId)
                ? $this->resourceKind->getMetadataById($metadataId)
                : $this->metadataRepository->findOne($metadataId);
            foreach ($metadataKind->getConstraints() as $constraintName => $constraintArgument) {
                $constraint = $this->metadataConstraintManager->get($constraintName);
                $constraint->validateAll($constraintArgument, $values);
            }
        });
        return true;
    }
}
