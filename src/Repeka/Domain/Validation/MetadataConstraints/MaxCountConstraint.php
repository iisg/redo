<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Assert\Assertion;
use Repeka\Domain\Entity\MetadataControl;
use Respect\Validation\Validator;

class MaxCountConstraint extends AbstractMetadataConstraint {
    public function getSupportedControls(): array {
        return MetadataControl::all();
    }

    public function isConfigValid($maxCount): bool {
        return Validator::intType()->min(0)->validate($maxCount);
    }

    public function isValueValid($maxCount, $resource): bool {
        Assertion::isArray($resource);
        $count = count($resource);
        return ($maxCount == 0) || ($count <= $maxCount);
    }
}
