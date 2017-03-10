<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\MetadataConstraintProvider;
use Respect\Validation\Rules\AbstractRule;

class MetadataValuesSatisfyConstraintsRule extends AbstractRule {
    /** @var ResourceKind */
    private $resourceKind;
    /** @var MetadataConstraintProvider */
    private $metadataConstraintProvider;

    public function __construct(MetadataConstraintProvider $metadataConstraintProvider) {
        $this->metadataConstraintProvider = $metadataConstraintProvider;
    }

    public function forResourceKind($resourceKind): MetadataValuesSatisfyConstraintsRule {
        $instance = new self($this->metadataConstraintProvider);
        $instance->resourceKind = $resourceKind;
        return $instance;
    }

    public function validate($input) {
        Assertion::notNull(
            $this->resourceKind,
            'Resource kind not set. Use forResourceKind() to create validator for specific resource kind first.'
        );
        foreach ($input as $baseId => $metadataValue) {
            $metadataKind = $this->resourceKind->getMetadataByBaseId($baseId);
            foreach ($metadataKind->getConstraints() as $constraintName => $constraintArgument) {
                $constraint = $this->metadataConstraintProvider->get($constraintName);
                if (!$constraint->validateValue($constraintArgument, $metadataValue)) {
                    return false;
                }
            }
        }
        return true;
    }
}
