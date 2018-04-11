<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Tests\IntegrationTestCase;

class ResourceKindRepositoryIntegrationTest extends IntegrationTestCase {
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
}
