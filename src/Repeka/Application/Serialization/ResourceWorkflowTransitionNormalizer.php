<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;

class ResourceWorkflowTransitionNormalizer extends LabeledNormalizer {
    /** @param ResourceWorkflowTransition $resourceWorkflowTransition */
    public function normalize($resourceWorkflowTransition, $format = null, array $context = []) {
        return parent::normalize($resourceWorkflowTransition, $format, $context);
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowTransition;
    }
}
