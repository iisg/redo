<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;

class ResourceWorkflowPlaceNormalizer extends LabeledNormalizer {
    public function normalize($place, $format = null, array $context = []) {
        $data = parent::normalize($place, $format, $context);
        $data['pluginsConfig'] = $this->getPluginsConfig($data);
        return $data;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowPlace;
    }

    private function getPluginsConfig(array $data) {
        $configs = array_map(
            function ($element) {
                if (is_array($element)) {
                    return $this->emptyArrayAsObject($element);
                } else {
                    return $element;
                }
            },
            $data['pluginsConfig']
        );
        return $this->emptyArrayAsObject($configs);
    }
}
