<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Tests\IntegrationTestCase;

class ResourceRepositoryIntegrationTest extends IntegrationTestCase {
    /** @var EntityRepository|ResourceRepository */
    private $resourceRepository;
    /** @var EntityManagerInterface */
    private $em;

    public function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->em = $this->container->get('doctrine.orm.entity_manager');
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
        $ebooksCategoryId = $this->getEbooksCategoryResourceId();
        $this->assertNotNull($ebooksCategoryId);
        $ebooksChildren = $this->resourceRepository->findChildren($ebooksCategoryId);
        $this->assertCount(2, $ebooksChildren);
    }

    public function testDelete() {
        $ebooksCategoryId = $this->getEbooksCategoryResourceId();
        $ebooksCategory = $this->resourceRepository->findOne($ebooksCategoryId);
        $countBefore = count($this->resourceRepository->findAll());
        $this->resourceRepository->delete($ebooksCategory);
        $this->em->flush();
        $this->assertCount($countBefore - 1, $this->resourceRepository->findAll());
        $this->assertFalse($this->resourceRepository->exists($ebooksCategoryId));
    }

    private function getEbooksCategoryResourceId(): int {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $categoryNameMetadataId = $connection->query("SELECT id FROM metadata WHERE label->'EN' = '\"Category name\"';")->fetch()['id'];
        $ebooksCategoryId = $connection
            ->query("SELECT id FROM resource WHERE contents->'{$categoryNameMetadataId}' = '[\"E-booki\"]'")->fetch()['id'];
        $this->assertNotNull($ebooksCategoryId);
        return $ebooksCategoryId;
    }
}
