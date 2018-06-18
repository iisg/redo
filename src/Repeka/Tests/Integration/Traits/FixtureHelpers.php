<?php
namespace Repeka\Tests\Integration\Traits;

use Psr\Container\ContainerInterface;
use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\User\UserQuery;

/**
 * @property ContainerInterface $container
 * @method mixed handleCommand(Command $command)
 */
trait FixtureHelpers {
    private function getPhpBookResource(): ResourceEntity {
        return $this->findResourceByContents(['Tytuł' => 'PHP - to można leczyć!']);
    }

    private function findResourceByContents(array $contents): ResourceEntity {
        $filters = ResourceContents::fromArray($contents)->withMetadataNamesMappedToIds($this->container->get(MetadataRepository::class));
        $query = ResourceListQuery::builder()->filterByContents($filters)->build();
        return $this->getResourceRepository()->findByQuery($query)[0];
    }

    private function getAdminUser(): User {
        return $this->handleCommand(new UserQuery(AdminAccountFixture::ADMIN_USER_ID));
    }

    protected function findMetadataByName(string $name, string $resourceClass = 'books'): Metadata {
        return $this->container->get(MetadataRepository::class)
            ->findByQuery(MetadataListQuery::builder()->filterByResourceClass($resourceClass)->filterByName($name)->build())[0];
    }

    protected function getResourceRepository(): ResourceRepository {
        return $this->container->get(ResourceRepository::class);
    }
}
