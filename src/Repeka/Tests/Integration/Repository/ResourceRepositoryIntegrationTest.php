<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Tests\IntegrationTestCase;

class ResourceRepositoryIntegrationTest extends IntegrationTestCase {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->container->get('repository.resource');
        $this->loadAllFixtures();
    }

    public function testFindAll() {
        $resources = $this->resourceRepository->findAll();
        $this->assertCount(6, $resources); // 1 per every user + fixtures
    }

    public function testFindAllNonSystemResources() {
        $resources = $this->resourceRepository->findAllNonSystemResources();
        $this->assertCount(3, $resources);
        $this->assertNotEquals(SystemResourceKind::USER, $resources[0]->getKind()->getId());
    }
}
