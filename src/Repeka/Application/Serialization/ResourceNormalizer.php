<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ResourceNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TransitionPossibilityChecker */
    private $transitionPossibilityChecker;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TransitionPossibilityChecker $transitionPossibilityChecker
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->transitionPossibilityChecker = $transitionPossibilityChecker;
    }

    /**
     * @param $resource ResourceEntity
     * @inheritdoc
     */
    public function normalize($resource, $format = null, array $context = []) {
        $normalized = [
            'id' => $resource->getId(),
            'kindId' => $resource->getKind()->getId(),
            'contents' => $resource->getContents()->toArray(),
            'resourceClass' => $resource->getResourceClass(),
        ];
        $availableTransitions = [SystemTransition::UPDATE()->toTransition($resource->getKind(), $resource)];
        $normalizerFunc = [$this->normalizer, 'normalize'];
        if ($resource->hasWorkflow()) {
            $workflow = $resource->getWorkflow();
            $normalized['currentPlaces'] = array_map($normalizerFunc, $workflow->getPlaces($resource));
            $normalized['blockedTransitions'] = array_map(
                $normalizerFunc,
                $this->getBlockedTransitions($resource, $this->tokenStorage->getToken()->getUser())
            );
            $normalized['transitionAssigneeMetadata'] = $this->getTransitionAssigneeMetadata($resource);
            $availableTransitions = array_merge($workflow->getTransitions($resource), $availableTransitions);
        }
        $normalized['availableTransitions'] = array_map($normalizerFunc, $availableTransitions);
        return $normalized;
    }

    /** @return TransitionPossibilityCheckResult[] */
    public function getBlockedTransitions(ResourceEntity $resource, User $currentUser): array {
        /** @var TransitionPossibilityCheckResult */
        $failedPossibilityChecks = [];
        $transitionsToCheck = $resource->getWorkflow()->getTransitions($resource);
        $transitionsToCheck[] = SystemTransition::UPDATE()->toTransition($resource->getKind(), $resource);
        foreach ($transitionsToCheck as $transition) {
            $result = $this->transitionPossibilityChecker->check($resource, $resource->getContents(), $transition, $currentUser);
            if (!$result->isTransitionPossible()) {
                $failedPossibilityChecks[$transition->getId()] = $result;
            }
        }
        return $failedPossibilityChecks;
    }

    /**
     * Gets possible transitions from resource's current state and returns them grouped by IDs of metadata that determine their assignees.
     * Useful for displaying which transitions are made available for users related to resource through given metadata.
     * @return array in form of metadataId => transition[]
     */
    private function getTransitionAssigneeMetadata(ResourceEntity $resource): array {
        $metadataTransitionMap = [];
        foreach ($resource->getWorkflow()->getTransitions($resource) as $transition) {
            $assigneeMetadataIds = $this->transitionPossibilityChecker->getAssigneeMetadataIds($resource, $transition);
            $assigneeMetadataIds =
                array_merge($assigneeMetadataIds, $this->transitionPossibilityChecker->getAutoAssignMetadataIds($resource, $transition));
            foreach ($assigneeMetadataIds as $metadataId) {
                if (!array_key_exists($metadataId, $metadataTransitionMap)) {
                    $metadataTransitionMap[$metadataId] = [];
                }
                $metadataTransitionMap[$metadataId][] = $this->normalizer->normalize($transition);
            }
        }
        return $metadataTransitionMap;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceEntity;
    }
}
