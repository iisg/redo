<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResourceNormalizer implements NormalizerInterface {
    /** @var ResourceWorkflowRepository */
    private $resourceWorkflowRepository;

    public function __construct(ResourceWorkflowRepository $resourceWorkflowRepository) {
        $this->resourceWorkflowRepository = $resourceWorkflowRepository;
    }

    /**
     * @param $resource ResourceEntity
     * @inheritdoc
     */
    public function normalize($resource, $format = null, array $context = []) {
        $workflow = $this->resourceWorkflowRepository->get($resource);
        return [
            'id' => $resource->getId(),
            'kindId' => $resource->getKind()->getId(),
            'contents' => $resource->getContents(),
            'marking' => $workflow->getCurrentMarking($resource),
            'transitions' => $workflow->getEnabledTransitions($resource),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceEntity;
    }
}
