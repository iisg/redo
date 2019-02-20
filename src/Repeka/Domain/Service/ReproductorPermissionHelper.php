<?php
namespace Repeka\Domain\Service;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;

class ReproductorPermissionHelper {
    /** @var CommandBus */
    private $commandBus;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(CommandBus $commandBus, ResourceKindRepository $resourceKindRepository) {
        $this->commandBus = $commandBus;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /** @return ResourceEntity[]|\Traversable */
    public function getCollectionsWhereUserIsReproductor(User $user, ?ResourceKind $resourceKind = null) {
        $query = ResourceListQuery::builder()->setPermissionMetadataId(SystemMetadata::REPRODUCTOR)->setExecutor($user)->build();
        $resources = $this->commandBus->handle($query);
        if ($resourceKind) {
            $resources = array_values(
                array_filter(
                    iterator_to_array($resources),
                    function (ResourceEntity $resource) use ($resourceKind) {
                        return in_array($resourceKind->getId(), $this->getAllowedSubresourceKindIds($resource));
                    }
                )
            );
        }
        return $resources;
    }

    /** @return ResourceKind[] */
    public function getResourceKindsWhichResourcesUserCanCreate(User $user): array {
        $allowedSubresourceKindIds = array_map(
            function (ResourceEntity $resource) {
                return $this->getAllowedSubresourceKindIds($resource);
            },
            iterator_to_array($this->getCollectionsWhereUserIsReproductor($user))
        );
        if ($allowedSubresourceKindIds) {
            $allowedSubresourceKindIds = array_unique(call_user_func_array('array_merge', $allowedSubresourceKindIds));
            $resourceKindsQuery = ResourceKindListQuery::builder()->filterByIds($allowedSubresourceKindIds)->build();
            $resourceKinds = $this->commandBus->handle($resourceKindsQuery);
            return $resourceKinds;
        } else {
            return [];
        }
    }

    private function getAllowedSubresourceKindIds(ResourceEntity $resource): array {
        return $resource->getKind()->getMetadataByIdOrName(SystemMetadata::PARENT)->getConstraints()['resourceKind'] ?? [];
    }
}
