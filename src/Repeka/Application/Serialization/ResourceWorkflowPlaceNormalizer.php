<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;

class ResourceWorkflowPlaceNormalizer extends LabeledNormalizer {
    public function normalize($place, $format = null, array $context = []) {
        return parent::normalize($place, $format, $context);
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowPlace;
    }
}
