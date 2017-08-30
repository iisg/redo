<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Respect\Validation\Rules\AbstractRule;

class MetadataValuesSatisfyConstraintsRule extends AbstractRule {
    /** @var ResourceKind */
    private $resourceKind;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;

    public function __construct(MetadataConstraintManager $metadataConstraintManager) {
        $this->metadataConstraintManager = $metadataConstraintManager;
    }

    public function forResourceKind($resourceKind): MetadataValuesSatisfyConstraintsRule {
        $instance = new self($this->metadataConstraintManager);
        $instance->resourceKind = $resourceKind;
        return $instance;
    }

    public function validate($input) {
        Assertion::notNull(
            $this->resourceKind,
            'Resource kind not set. Use forResourceKind() to create validator for specific resource kind first.'
        );
        foreach ($input as $baseId => $metadataValues) {
            $metadataKind = $this->resourceKind->getMetadataByBaseId($baseId);
            foreach ($metadataKind->getConstraints() as $constraintName => $constraintArgument) {
                $constraint = $this->metadataConstraintManager->get($constraintName);
                if (!$constraint->isValueValid($constraintArgument, $metadataValues)) {
                    return false;
                }
            }
        }
        return true;
    }
}
