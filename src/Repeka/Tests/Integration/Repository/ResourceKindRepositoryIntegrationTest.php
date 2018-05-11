<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class ResourceKindRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var EntityRepository|ResourceKindRepository */
    private $resourceKindRepository;

    /** @before */
    public function before() {
        $this->resourceKindRepository = $this->container->get(ResourceKindRepository::class);
        $this->loadAllFixtures();
    }

    public function testFindAll() {
        $resourceKinds = $this->resourceKindRepository->findAll();
        $this->assertCount(7, $resourceKinds);
    }

    public function testCountByMetadata() {
        $metadata = $this->handleCommand(new MetadataGetQuery(2));
        $resourceKindsCount = $this->resourceKindRepository->countByMetadata($metadata);
        $this->assertEquals(1, $resourceKindsCount);
    }

    public function testCountByParentMetadata() {
        $resourceKindsCount = $this->resourceKindRepository->countByMetadata(SystemMetadata::PARENT()->toMetadata());
        $this->assertGreaterThan(2, $resourceKindsCount);
    }

    public function testFindAllByBookResourceClass() {
        $query = ResourceKindListQuery::builder()->filterByResourceClass('books')->build();
        $resourceKindList = $this->resourceKindRepository->findByQuery($query);
        $this->assertCount(3, $resourceKindList);
        foreach ($resourceKindList as $resourceKind) {
            $this->assertNotEquals(SystemResourceKind::USER, $resourceKind->getId());
        }
    }

    public function testFindByEmptyQuery() {
        $resourceKindList = $this->resourceKindRepository->findByQuery(ResourceKindListQuery::builder()->build());
        $this->assertCount(7, $resourceKindList);
    }

    public function testFindByMetadataId() {
        $metadata = $this->findMetadataByName('Opis');
        $resourceKindListQuery = ResourceKindListQuery::builder()->filterByMetadataId($metadata->getId())->build();
        $resourceKindList = $this->resourceKindRepository->findByQuery($resourceKindListQuery);
        $this->assertCount(1, $resourceKindList);
    }
}
