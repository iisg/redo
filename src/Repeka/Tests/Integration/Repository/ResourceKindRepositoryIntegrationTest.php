<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ResourceKindRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var EntityRepository|ResourceKindRepository */
    private $resourceKindRepository;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->resourceKindRepository = $this->container->get(ResourceKindRepository::class);
    }

    public function testFindAll() {
        $resourceKinds = $this->resourceKindRepository->findAll();
        $this->assertCount($this->resourceKindRepository->count([]), $resourceKinds);
    }

    public function testCountByMetadata() {
        $metadata = $this->handleCommandBypassingFirewall(new MetadataGetQuery(2));
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
        $this->assertCount($this->resourceKindRepository->count([]), $resourceKindList);
    }

    public function testFindByMetadataId() {
        $metadata = $this->findMetadataByName('Opis');
        $resourceKindListQuery = ResourceKindListQuery::builder()->filterByMetadataId($metadata->getId())->build();
        $resourceKindList = $this->resourceKindRepository->findByQuery($resourceKindListQuery);
        $this->assertCount(1, $resourceKindList);
    }

    public function testFindByUnslugifiedName() {
        $this->assertEquals($this->resourceKindRepository->findByName('bóók '), $this->resourceKindRepository->findByName('book'));
    }

    public function testFindForbiddenBookByName() {
        $query = ResourceKindListQuery::builder()->filterByNames(['forbidden_book'])->build();
        $resourceKindList = $this->resourceKindRepository->findByQuery($query);
        $this->assertCount(1, $resourceKindList);
    }

    public function testFindByWorkflowId() {
        $query = ResourceKindListQuery::builder()->filterByResourceClass('books')->filterByWorkflowId(1)->build();
        $resourceKindList = $this->resourceKindRepository->findByQuery($query);
        $this->assertCount(1, $resourceKindList);
    }

    public function testSortingByIdFilteringByResourceClass() {
        $resourceKindListQuery = ResourceKindListQuery::builder()
            ->filterByResourceClass('books')
            ->sortBy([['columnId' => 'id', 'direction' => 'ASC', 'language' => 'PL']])
            ->build();
        $resourceKindList = $this->resourceKindRepository->findByQuery($resourceKindListQuery);
        $this->assertCount(3, $resourceKindList);
    }

    public function testRemoveEveryResourceKindsUsageInOtherResourceKinds() {
        $id = 2;
        $searchedResourceKindId = 3;
        $resourceKindForRemoval = $this->createMock(ResourceKind::class);
        $resourceKindForRemoval->method('getId')->willReturn($id);
        $resourceKind = $this->resourceKindRepository->findOne($searchedResourceKindId);
        foreach ($resourceKind->getMetadataList() as $metadata) {
            if ($metadata->getId() == SystemMetadata::PARENT) {
                $this->assertContains($id, $metadata->getConstraints()['resourceKind']);
            }
        }
        $this->resourceKindRepository->removeEveryResourceKindsUsageInOtherResourceKinds($resourceKindForRemoval);
        $this->resetEntityManager($this->resourceKindRepository);
        $afterRemovalResourceKind = $this->resourceKindRepository->findOne($searchedResourceKindId);
        foreach ($afterRemovalResourceKind->getMetadataList() as $metadata) {
            if ($metadata->getId() == SystemMetadata::PARENT) {
                $this->assertNotContains($id, $metadata->getConstraints()['resourceKind']);
            }
        }
    }
}
