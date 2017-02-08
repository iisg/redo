<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ResourceWorkflowNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $resourceWorkflow ResourceWorkflow
     * @inheritdoc
     */
    public function normalize($resourceWorkflow, $format = null, array $context = []) {
        return [
            'id' => $resourceWorkflow->getId(),
            'name' => $this->emptyArrayAsObject($resourceWorkflow->getName()),
            'places' => array_map(function (ResourceWorkflowPlace $place) {
                return $this->normalizer->normalize($place);
            }, $resourceWorkflow->getPlaces()),
            'transitions' => array_map(function (ResourceWorkflowTransition $transition) {
                return $this->normalizer->normalize($transition);
            }, $resourceWorkflow->getTransitions()),
            'diagram' => $resourceWorkflow->getDiagram(),
            'thumbnail' => $resourceWorkflow->getThumbnail(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflow;
    }
}
