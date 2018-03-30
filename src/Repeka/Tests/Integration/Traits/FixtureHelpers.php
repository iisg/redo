<?php
namespace Repeka\Tests\Integration\Traits;

use Psr\Container\ContainerInterface;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\User\UserQuery;

/**
 * @property ContainerInterface $container
 * @method mixed handleCommand(Command $command)
 */
trait FixtureHelpers {
    /** @SuppressWarnings("PHPMD.UnusedLocalVariable") */
    private function getPhpBookResource(): ResourceEntity {
        $query = ResourceListQuery::builder()->filterByResourceClasses(['books'])->build();
        foreach ($this->getResourceRepository()->findByQuery($query) as $resource) {
            $isPhpBook = $resource->getContents()->reduceAllValues(function ($value, $metadataId, $isPhpBook) {
                return $isPhpBook || $value == 'PHP - to można leczyć!';
            });
            if ($isPhpBook) {
                return $resource;
            }
        }
        throw new \ErrorException("Resource not found");
    }

    private function getAdminUser(): User {
        return $this->handleCommand(new UserQuery(1));
    }

    protected function findMetadataByName(string $name, string $resourceClass = 'books'): Metadata {
        /** @var Metadata[] $metadataList */
        $metadataList = $this->handleCommand(MetadataListQuery::builder()->filterByResourceClass($resourceClass)->build());
        foreach ($metadataList as $metadata) {
            if ($metadata->getName() == $name) {
                return $metadata;
            }
        }
        $this->fail("Metadata $name not found");
    }

    protected function getResourceRepository(): ResourceRepository {
        return $this->container->get(ResourceRepository::class);
    }
}
