<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Respect\Validation\Validator;

class MinMaxValueConstraint extends AbstractMetadataConstraint implements ConfigurableMetadataConstraint {
    public function getSupportedControls(): array {
        return [MetadataControl::INTEGER, MetadataControl::DOUBLE];
    }

    public function getConstraintName(): string {
        return 'minMaxValue';
    }

    public function isConfigValid($minMaxValue): bool {
        $valMin = Validator::key('min', Validator::intVal(), false);
        $valMax = Validator::key('max', Validator::intVal(), false);
        if (!Validator::keySet($valMin, $valMax)->validate($minMaxValue)) {
            return false;
        } else {
            return array_key_exists('max', $minMaxValue) && array_key_exists('min', $minMaxValue) ?
                (isset($minMaxValue['min']) & isset($minMaxValue['max']) & ($minMaxValue['max'] < $minMaxValue['min']) ?
                    false : true) : true;
        }
    }

    /** @inheritdoc */
    public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource): void {
        $config = $metadata->getConstraints()[$this->getConstraintName()] ?? [];
        if (!Validator::numericVal()->validate($metadataValue)) {
            throw new \InvalidArgumentException('Value must be a number');
        }
        if (array_key_exists('min', $config)) {
            if (isset($config['min']) & $config['min'] > $metadataValue) {
                throw new \DomainException('Value must be higher than minimal constraint');
            }
        }
        if (array_key_exists('max', $config)) {
            if (isset($config['max']) & $config['max'] < $metadataValue) {
                throw new \DomainException('Value must be less than maximal constraint');
            }
        }
    }
}
