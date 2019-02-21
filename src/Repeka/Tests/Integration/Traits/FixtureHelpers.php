<?php
namespace Repeka\Tests\Integration\Traits;

use Psr\Container\ContainerInterface;
use Repeka\Application\Entity\UserEntity;
use Repeka\DeveloperBundle\DataFixtures\Redo\AdminAccountFixture;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Domain\UseCase\User\UserQuery;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @property ContainerInterface $container
 * @method mixed handleCommandBypassingFirewall(Command $command)
 */
trait FixtureHelpers {
    private function getPhpBookResource(): ResourceEntity {
        return $this->findResourceByContents(['Tytuł' => 'PHP - to można leczyć!']);
    }

    private function findResourceByContents(array $contents): ResourceEntity {
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $filters = ResourceContents::fromArray($contents)->withMetadataNamesMappedToIds($metadataRepository);
        $query = ResourceListQuery::builder()->filterByContents($filters)->build();
        return $this->getResourceRepository()->findByQuery($query)[0];
    }

    private function getAdminUser(): User {
        return $this->handleCommandBypassingFirewall(new UserQuery(AdminAccountFixture::ADMIN_USER_ID));
    }

    protected function findMetadataByName(string $name): Metadata {
        return $this->container->get(MetadataRepository::class)->findByName($name);
    }

    private function getBudynekUser(): UserEntity {
        return $this->getUserByName('budynek');
    }

    private function getSkanerUser(): UserEntity {
        return $this->getUserByName('skaner');
    }

    private function getUserByName(string $name): UserEntity {
        /** @var UserEntity[] $users */
        $users = $this->handleCommandBypassingFirewall(new UserListQuery());
        foreach ($users as $user) {
            if ($user->getUsername() == $name) {
                return $user;
            }
        }
        $this->fail("User not found");
    }

    private function getUnauthenticatedUser(): UserEntity {
        $unauthenticatedUserData = $this->getResourceRepository()->findOne(-1);
        $unauthenticatedUser = new UserEntity();
        $unauthenticatedUser->setUserData($unauthenticatedUserData);
        EntityUtils::forceSetId($unauthenticatedUser, SystemResource::UNAUTHENTICATED_USER);
        return $unauthenticatedUser;
    }

    protected function getResourceRepository(): ResourceRepository {
        return $this->container->get(ResourceRepository::class);
    }

    protected function getMetadataRepository(): MetadataRepository {
        return $this->container->get(MetadataRepository::class);
    }

    private function addSupportForResourceKindToMetadata(int $metadataId, int $resourceKindId) {
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $visibilityMetadata = $metadataRepository->findOne($metadataId);
        $constraints = $visibilityMetadata->getConstraints();
        $supportedResourceKinds = $constraints['resourceKind'] ?? [];
        $supportedResourceKinds[] = $resourceKindId;
        $constraints['resourceKind'] = $supportedResourceKinds;
        $query = new MetadataUpdateCommand(
            $visibilityMetadata,
            $visibilityMetadata->getLabel(),
            $visibilityMetadata->getDescription(),
            $visibilityMetadata->getPlaceholder(),
            $constraints,
            $visibilityMetadata->getGroupId(),
            $visibilityMetadata->getDisplayStrategy(),
            $visibilityMetadata->isShownInBrief(),
            $visibilityMetadata->isCopiedToChildResource()
        );
        $this->handleCommandBypassingFirewall($query);
    }

    protected function getResourceKindRepository(): ResourceKindRepository {
        return $this->container->get(ResourceKindRepository::class);
    }

    protected function unlockAllMetadata(ResourceWorkflow $workflow): void {
        $placesWithNoLockedMetadata = array_map(
            function (ResourceWorkflowPlace $place) {
                $array = $place->toArray();
                $array['lockedMetadataIds'] = [];
                return $array;
            },
            $workflow->getPlaces()
        );
        $this->handleCommandBypassingFirewall(
            new ResourceWorkflowUpdateCommand(
                $workflow,
                $workflow->getName(),
                $placesWithNoLockedMetadata,
                $workflow->getTransitions(),
                $workflow->getDiagram(),
                $workflow->getThumbnail()
            )
        );
    }
}
