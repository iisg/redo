<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;

class ResourceWorkflowPlaceNormalizer extends LabeledNormalizer {
    public function normalize($place, $format = null, array $context = []) {
        $data = parent::normalize($place, $format, $context);
        $data['pluginsConfig'] = $this->emptyArrayAsObject($data['pluginsConfig']);
        return $data;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowPlace;
    }
}
