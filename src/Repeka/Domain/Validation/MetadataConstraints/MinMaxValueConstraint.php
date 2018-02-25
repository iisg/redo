<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Respect\Validation\Validator;
use Repeka\Domain\Entity\MetadataControl;

class MinMaxValueConstraint extends AbstractMetadataConstraint {
    public function getSupportedControls(): array {
        return [MetadataControl::INTEGER];
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

    public function validateSingle(Metadata $metadata, $config, $metadataValue) {
        if (!Validator::intType()->validate($metadataValue)) {
            throw new \BadMethodCallException('Value must be an integer');
        }
        if (array_key_exists('min', $config)) {
            if (isset($config['min']) & $config['min'] > $metadataValue) {
                throw new \BadMethodCallException('Value must be higher than minimal constraint');
            }
        }
        if (array_key_exists('max', $config)) {
            if (isset($config['max']) & $config['max'] < $metadataValue) {
                throw new \BadMethodCallException('Value must be less than maximal constraint');
            }
        }
    }
}
