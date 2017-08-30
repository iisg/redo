<?php
namespace Repeka\Application\Validation;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class ContainerAwareMetadataConstraintManager implements MetadataConstraintManager {
    /** @var AbstractMetadataConstraint[] */
    private $rules = [];

    /** @var string[] Maps controls names to arrays of constraint names */
    private $applicableForControl = [];

    public function __construct() {
        $supportedControls = MetadataControl::all();
        $this->applicableForControl = $this->makeArray($supportedControls, []);
    }

    /**
     * Example:
     * makeArray(['a', 'b', 'c'], 'X') --> ['a' => 'X', 'b' => 'X', 'c' => 'X']
     */
    private function makeArray(array $keys, $value): array {
        $values = array_fill(0, count($keys), $value);
        return array_combine($keys, $values);
    }

    public function get(string $constraintName): AbstractMetadataConstraint {
        if (array_key_exists($constraintName, $this->rules)) {
            return $this->rules[$constraintName];
        } else {
            throw new \InvalidArgumentException("Rule for constraint '$constraintName' isn't registered");
        }
    }

    public function getSupportedConstraintNamesForControl(string $controlName): array {
        if (!array_key_exists($controlName, $this->applicableForControl)) {
            throw new \InvalidArgumentException("Control '$controlName' not supported");
        }
        return $this->applicableForControl[$controlName] ?? [];
    }

    public function getRequiredConstraintNamesMap(): array {
        return $this->applicableForControl;
    }

    public function register(AbstractMetadataConstraint $rule) {
        $constraintName = $rule->getConstraintName();
        if (array_key_exists($constraintName, $this->rules)) {
            throw new \InvalidArgumentException("Rule for constraint '$constraintName' already registered");
        }
        $this->rules[$constraintName] = $rule;
        $supportedControls = $rule->getSupportedControls();
        foreach ($supportedControls as $control) {
            if (!array_key_exists($control, $this->applicableForControl)) {
                throw new \InvalidArgumentException("Control '$control' not supported");
            }
            $this->applicableForControl[$control][] = $constraintName;
        }
    }
}
