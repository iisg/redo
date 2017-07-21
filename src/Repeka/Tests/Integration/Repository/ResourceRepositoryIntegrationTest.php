<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Tests\IntegrationTestCase;

class ResourceRepositoryIntegrationTest extends IntegrationTestCase {
    /** @var EntityRepository|ResourceRepository */
    private $resourceRepository;

    public function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->loadAllFixtures();
    }

    public function testFindAll() {
        $resources = $this->resourceRepository->findAll();
        $this->assertCount(9, $resources); // 1 per every user + fixtures
    }

    public function testFindAllNonSystemResources() {
        $resources = $this->resourceRepository->findAllNonSystemResources();
        $this->assertCount(6, $resources); // fixtures only
        foreach ($resources as $resource) {
            $this->assertNotEquals(SystemResourceKind::USER, $resource->getKind()->getId());
        }
    }

    public function testFindTopLevel() {
        $topLevelResources = $this->resourceRepository->findTopLevel();
        $this->assertCount(4, $topLevelResources);
    }

    public function testFindChildren() {
        $connection = $this->container->get('database_connection');
        $categoryNameMetadataId = $connection->query("SELECT id FROM metadata WHERE label->'EN' = '\"Category name\"';")->fetch()['id'];
        $ebooksParentId = $connection
            ->query("SELECT id FROM resource WHERE contents->'{$categoryNameMetadataId}' = '[\"E-booki\"]'")->fetch()['id'];
        $this->assertNotNull($ebooksParentId);
        $ebooksChildren = $this->resourceRepository->findChildren($ebooksParentId);
        $this->assertCount(2, $ebooksChildren);
    }
}
