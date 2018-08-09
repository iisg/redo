<?php
namespace Repeka\Application\Serialization;

use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ResourceNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use CurrentUserAware;
    use NormalizerAwareTrait;

    const DO_NOT_STRIP_RESOURCE_CONTENT = 'doNotStripResourceContent';

    /** @var TransitionPossibilityChecker */
    private $transitionPossibilityChecker;

    public function __construct(TransitionPossibilityChecker $transitionPossibilityChecker) {
        $this->transitionPossibilityChecker = $transitionPossibilityChecker;
    }

    /**
     * @param $resource ResourceEntity
     * @inheritdoc
     */
    public function normalize($resource, $format = null, array $context = []) {
        $returnTeaser = $this->shouldReturnTeaser($resource, $context);
        $normalized = [
            'id' => $resource->getId(),
            'kindId' => $resource->getKind()->getId(),
            'contents' => $this->getContentsArray($resource, $returnTeaser),
            'resourceClass' => $resource->getResourceClass(),
        ];
        if (!$returnTeaser) {
            $availableTransitions = [SystemTransition::UPDATE()->toTransition($resource->getKind(), $resource)];
            $normalizerFunc = [$this->normalizer, 'normalize'];
            if ($resource->hasWorkflow()) {
                $workflow = $resource->getWorkflow();
                $normalized['currentPlaces'] = array_map($normalizerFunc, $workflow->getPlaces($resource));
                $normalized['blockedTransitions'] = array_map(
                    $normalizerFunc,
                    $this->getBlockedTransitions($resource, $this->getCurrentUser())
                );
                $normalized['transitionAssigneeMetadata'] = $this->getTransitionAssigneeMetadata($resource);
                $availableTransitions = array_merge($workflow->getTransitions($resource), $availableTransitions);
            }
            $normalized['availableTransitions'] = array_map($normalizerFunc, $availableTransitions);
        }
        return $normalized;
    }

    private function shouldReturnTeaser(ResourceEntity $resource, array $context): bool {
        $user = $this->getCurrentUser();
        $doNotCheckRole = in_array(self::DO_NOT_STRIP_RESOURCE_CONTENT, $context);
        return !$doNotCheckRole && (!$user || !$user->hasRole(SystemRole::OPERATOR()->roleName($resource->getResourceClass())));
    }

    private function getContentsArray(ResourceEntity $resource, bool $teaser): array {
        if ($teaser) {
            return ResourceContents::fromArray([SystemMetadata::RESOURCE_LABEL => $resource->getValues(SystemMetadata::RESOURCE_LABEL)])
                ->toArray();
        } else {
            return $resource->getContents()->toArray();
        }
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
