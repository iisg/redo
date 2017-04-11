<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceWorkflowTransition;

class ResourceWorkflowTransitionNormalizer extends ResourceWorkflowPlaceNormalizer {
    /** @param ResourceWorkflowTransition $resourceWorkflowTransition */
    public function normalize($resourceWorkflowTransition, $format = null, array $context = []) {
        $data = parent::normalize($resourceWorkflowTransition, $format, $context);
        $data['permittedRoleIds'] = $resourceWorkflowTransition->getPermittedRoleIds();
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowTransition;
    }
}
