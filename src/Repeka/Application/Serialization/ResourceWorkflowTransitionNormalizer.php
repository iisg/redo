<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceWorkflowTransition;

class ResourceWorkflowTransitionNormalizer extends ResourceWorkflowPlaceNormalizer {
    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowTransition;
    }
}
