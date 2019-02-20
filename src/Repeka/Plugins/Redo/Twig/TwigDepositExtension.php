<?php
namespace Repeka\Plugins\Redo\Twig;

use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;

/**
 * All Twig extensions that helps to retrieve deposit data in frontend.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TwigDepositExtension extends \Twig_Extension {
    use CurrentUserAware;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceNormalizer */
    private $resourceNormalizer;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ReproductorPermissionHelper */
    private $reproductorPermissionHelper;

    public function __construct(
        ResourceKindRepository $resourceKindRepository,
        ResourceRepository $resourceRepository,
        MetadataRepository $metadataRepository,
        ResourceNormalizer $resourceNormalizer,
        ReproductorPermissionHelper $reproductorPermissionHelper
    ) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceRepository = $resourceRepository;
        $this->resourceNormalizer = $resourceNormalizer;
        $this->metadataRepository = $metadataRepository;
        $this->reproductorPermissionHelper = $reproductorPermissionHelper;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('canDepositAnyResource', [$this, 'canDepositAnyResource']),
            new \Twig_Function('resourcesToDeposit', [$this, 'allowedResourcesForResourceKind']),
            new \Twig_Function('availableTransitions', [$this, 'getAvailableTransitions']),
        ];
    }

    public function canDepositAnyResource() {
        return count($this->reproductorPermissionHelper->getCollectionsWhereUserIsReproductor($this->getCurrentUserOrThrow())) > 0;
    }

    /**
     * @param User $user
     * @param array $metadataIdsOrNames array of metadata ids or names that should be filtered by
     * @return ResourceKind[]
     */
    public function depositableResourceKinds(User $user, $metadataIdsOrNames) {
        /** @var PageResult $resources */
        $resources = $this->fetchFilteredByUserResources($user, $metadataIdsOrNames);
        $resourceKindIds = $this->allowedSubresourceKindIds($resources->getResults());
        if (empty($resourceKindIds)) {
            return [];
        }
        $resourceKindListQuery = ResourceKindListQuery::builder()
            ->filterByIds(array_unique($resourceKindIds))
            ->build();
        return array_values(
            array_filter(
                $this->resourceKindRepository->findByQuery($resourceKindListQuery),
                function (ResourceKind $resourceKind) {
                    return !!$resourceKind->getWorkflow();
                }
            )
        );
    }

    /**
     * @param User $user
     * @param array $metadataIdsOrNames array of metadata ids or names that should be filtered by
     * @return ResourceEntity[]
     */
    public function fetchFilteredByUserResources(User $user, $metadataIdsOrNames) {
        $resourceListQueryBuilder = ResourceListQuery::builder();
        foreach ($metadataIdsOrNames as $metadataIdOrName) {
            $metadata = $this->metadataRepository->findByNameOrId($metadataIdOrName);
            $userIdAndGroupIds = $user->getGroupIdsWithUserId();
            $resourceListQueryBuilder = $resourceListQueryBuilder
                ->filterByContents([$metadata->getId() => $userIdAndGroupIds]);
        }
        $resourceListQuery = $resourceListQueryBuilder->build();
        $resources = $this->resourceRepository->findByQuery($resourceListQuery);
        return $resources;
    }

    /**
     * @param ResourceKind $resourceKind
     * @param User $user
     * @param array $metadataIdsOrNames array of metadata ids or names that should be filtered by
     * @return \Repeka\Domain\Entity\ResourceEntity[]
     */
    public function allowedResourcesForResourceKind(ResourceKind $resourceKind, User $user, $metadataIdsOrNames) {
        $resources = $this->fetchFilteredByUserResources($user, $metadataIdsOrNames);
        return array_filter(
            $resources->getResults(),
            function ($resource) use ($resourceKind) {
                $parentMetadataConstraints = $this->getParentMetadataConstraints($resource);
                if (!empty($parentMetadataConstraints)) {
                    return in_array($resourceKind->getId(), $parentMetadataConstraints['resourceKind']);
                }
            }
        );
    }

    private function allowedSubresourceKindIds(array $resources) {
        $resourceKindIds = [];
        foreach ($resources as $resource) {
            $parentMetadataConstraints = $this->getParentMetadataConstraints($resource);
            if (!empty($parentMetadataConstraints)) {
                $resourceKindIds = array_merge($resourceKindIds, $parentMetadataConstraints['resourceKind']);
            }
        }
        return $resourceKindIds;
    }

    private function getParentMetadataConstraints(ResourceEntity $resource): array {
        $parentMetadataArray = array_filter(
            $resource->getKind()->getMetadataOverrides(),
            function ($metadata) {
                return $metadata['id'] == SystemMetadata::PARENT;
            }
        );
        $parentMetadataOverrides = array_values($parentMetadataArray)[0];
        $parentMetadata = SystemMetadata::PARENT()->toMetadata()->withOverrides($parentMetadataOverrides);
        return $parentMetadata->getConstraints();
    }

    /**
     * @param User $user
     * @param ResourceEntity $resource
     * @param array $metadataIdsOrNames array of metadata ids or names that should be filtered by. If not null, check if user is assignee
     * @return ResourceEntity[]
     */
    public function getAvailableTransitions(User $user, ResourceEntity $resource, $metadataIdsOrNames = null) {
        if (!$resource->hasWorkflow()) {
            return [];
        }
        if ($metadataIdsOrNames) {
            $contentsValues = [];
            foreach ($metadataIdsOrNames as $metadataIdOrName) {
                try {
                    $metadata = $resource->getKind()->getMetadataByIdOrName($metadataIdOrName);
                    $values = $resource->getContents()->getValues($metadata);
                    $values = array_map(
                        function ($metadataValue) {
                            /** @var MetadataValue $metadataValue */
                            return $metadataValue->getValue();
                        },
                        $values
                    );
                    $contentsValues = array_merge($contentsValues, $values);
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
            }
            if (!in_array($user->getUserData()->getId(), $contentsValues) && !in_array($user->getUserGroupsIds(), $contentsValues)) {
                return [];
            }
        }
        $availableTransitions = $resource->hasWorkflow() ? $resource->getWorkflow()->getTransitions($resource) : [];
        $blockedTransitions = $this->resourceNormalizer->getBlockedTransitions($resource, $user);
        $availableTransitions = array_filter(
            $availableTransitions,
            function ($transition) use ($blockedTransitions) {
                /**
                 * @var TransitionPossibilityCheckResult[] $blockedTransitions
                 * @var ResourceWorkflowTransition $transition
                 */
                return !array_key_exists($transition->getId(), $blockedTransitions)
                    || !$blockedTransitions[$transition->getId()]->isOtherUserAssigned();
            }
        );
        return $availableTransitions;
    }
}
