<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ResourceNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var ResourceWorkflowRepository */
    private $resourceWorkflowRepository;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(ResourceWorkflowRepository $resourceWorkflowRepository, TokenStorageInterface $tokenStorage) {
        $this->resourceWorkflowRepository = $resourceWorkflowRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param $resource ResourceEntity
     * @inheritdoc
     */
    public function normalize($resource, $format = null, array $context = []) {
        $normalized = [
            'id' => $resource->getId(),
            'kindId' => $resource->getKind()->getId(),
            'contents' => $resource->getContents(),
        ];
        if ($resource->hasWorkflow()) {
            $workflow = $resource->getWorkflow();
            $normalizerFunc = [$this->normalizer, 'normalize'];
            $normalized['currentPlaces'] = array_map($normalizerFunc, $workflow->getPlaces($resource));
            $normalized['availableTransitions'] = array_map($normalizerFunc, $workflow->getTransitions($resource));
            $normalized['unsatisfiedTransitions'] = array_map(
                $normalizerFunc,
                $workflow->getUnsatisfiedTransitionExplanations($resource, $this->tokenStorage->getToken()->getUser())
            );
        }
        return $normalized;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceEntity;
    }
}
