<?php
namespace Repeka\Domain\Validation;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Validation\MetadataConstraints\ConfigurableMetadataConstraint;

class MetadataConstraintManager {
    /** @var AbstractMetadataConstraint[] */
    private $constraints = [];

    /** @var string[][] Maps controls names to arrays of constraint names */
    private $applicableForControl = [];

    public function __construct(iterable $constraints) {
        $this->registerAll($constraints);
    }

    /** @return AbstractMetadataConstraint|ConfigurableMetadataConstraint */
    public function get(string $constraintName) {
        if (array_key_exists($constraintName, $this->constraints)) {
            return $this->constraints[$constraintName];
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

    /** @return AbstractMetadataConstraint[] */
    public function getConstraints(): array {
        return $this->constraints;
    }

    private function registerAll(iterable $constraints) {
        $supportedControls = MetadataControl::toArray();
        $this->applicableForControl = ArrayUtils::combineArrayWithSingleValue($supportedControls, []);
        foreach ($constraints as $constraint) {
            $constraintName = $constraint->getConstraintName();
            if (array_key_exists($constraintName, $this->constraints)) {
                throw new \InvalidArgumentException("Rule for constraint '$constraintName' already registered");
            }
            $this->constraints[$constraintName] = $constraint;
            $supportedControls = $constraint->getSupportedControls();
            foreach ($supportedControls as $control) {
                if (!array_key_exists($control, $this->applicableForControl)) {
                    throw new \InvalidArgumentException("Control '$control' not supported");
                }
                $this->applicableForControl[$control][] = $constraintName;
            }
        }
    }
}
