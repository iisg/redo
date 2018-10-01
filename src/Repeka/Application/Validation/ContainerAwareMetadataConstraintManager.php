<?php
namespace Repeka\Application\Validation;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class ContainerAwareMetadataConstraintManager implements MetadataConstraintManager {
    /** @var iterable|AbstractMetadataConstraint[] */
    private $constraints;

    /** @var AbstractMetadataConstraint[] */
    private $rules = [];

    /** @var string[][] Maps controls names to arrays of constraint names */
    private $applicableForControl = [];

    /** @var AbstractMetadataConstraint[][] Maps controls names to arrays of mandatory constraints */
    private $mandatoryConstraints = [];


    public function __construct(iterable $constraints) {
        $this->constraints = $constraints;
        $this->registerAll();
    }

    public function get(string $constraintName): AbstractMetadataConstraint {
        if (array_key_exists($constraintName, $this->rules)) {
            return $this->rules[$constraintName];
        } else {
            throw new \InvalidArgumentException("Rule for constraint '$constraintName' isn't registered");
        }
    }

    /** * @return AbstractMetadataConstraint[] */
    public function getMandatoryConstraintsForControl(string $controlName): array {
        return $this->mandatoryConstraints[$controlName] ?? [];
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

    private function registerAll() {
        $supportedControls = MetadataControl::toArray();
        $this->applicableForControl = ArrayUtils::combineArrayWithSingleValue($supportedControls, []);
        foreach ($this->constraints as $rule) {
            $constraintName = $rule->getConstraintName();
            if (array_key_exists($constraintName, $this->rules)) {
                throw new \InvalidArgumentException("Rule for constraint '$constraintName' already registered");
            }
            $this->rules[$constraintName] = $rule;
            $supportedControls = $rule->getSupportedControls();
            $isMandatory = $rule->isMandatory();
            foreach ($supportedControls as $control) {
                if (!array_key_exists($control, $this->applicableForControl)) {
                    throw new \InvalidArgumentException("Control '$control' not supported");
                }
                $this->applicableForControl[$control][] = $constraintName;
                if ($isMandatory) {
                    if (!array_key_exists($control, $this->mandatoryConstraints)) {
                        $this->mandatoryConstraints[$control] = [];
                    }
                    $this->mandatoryConstraints[$control][] = $rule;
                }
            }
        }
    }
}
