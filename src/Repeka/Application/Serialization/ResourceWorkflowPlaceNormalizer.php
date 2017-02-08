<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceWorkflowPlace;

class ResourceWorkflowPlaceNormalizer extends AbstractNormalizer {
    /**
     * @param $resourceWorkflowPlace ResourceWorkflowPlace
     * @inheritdoc
     */
    public function normalize($resourceWorkflowPlace, $format = null, array $context = []) {
        $data = $resourceWorkflowPlace->toArray();
        $data['label'] = $this->emptyArrayAsObject($resourceWorkflowPlace->getLabel());
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowPlace;
    }
}
