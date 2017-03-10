<?php
namespace Repeka\Application\Validation;

use Repeka\Domain\Validation\MetadataConstraintProvider;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class ContainerAwareMetadataConstraintProvider implements MetadataConstraintProvider {
    /** @var AbstractMetadataConstraint[] */
    private $rules = [];

    public function get(string $constraintName): AbstractMetadataConstraint {
        if (array_key_exists($constraintName, $this->rules)) {
            return $this->rules[$constraintName];
        } else {
            throw new \InvalidArgumentException("Rule for constraint '$constraintName' isn't registered");
        }
    }

    public function register(AbstractMetadataConstraint $rule) {
        $constraintName = $rule->getConstraintName();
        if (array_key_exists($constraintName, $this->rules)) {
            throw new \InvalidArgumentException("Rule for constraint '$constraintName' already registered");
        }
        $this->rules[$constraintName] = $rule;
    }
}
