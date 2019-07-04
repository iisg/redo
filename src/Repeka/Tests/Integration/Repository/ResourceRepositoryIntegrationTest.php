<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\Integration\UseCase\Resource\UpdateType;
use Repeka\Tests\IntegrationTestCase;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class ResourceRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var EntityRepository|ResourceRepository */
    private $resourceRepository;
    /** @var UserRepository */
    private $userRepository;
    /** @var Metadata */
    private $titleMetadata;

    public function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->loadAllFixtures();
        $this->titleMetadata = $this->findMetadataByName('Tytuł');
        $this->unlockAllMetadata($this->getPhpBookResource()->getWorkflow());
    }

    public function testFindByWorkflowPlaceWhenResourceHasManyWorkflowPlaces() {
        $placeA = 'aaaaaaaaa';
        $placeB = 'bbbbbbbbb';
        $book = $this->getPhpBookResource();
        $book->setMarking([$placeA => true, $placeB => 1]);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds([$placeA])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(1, $paginatedResources->getTotalCount());
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds([$placeB])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(1, $paginatedResources->getTotalCount());
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds([$placeA, $placeB])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(1, $paginatedResources->getTotalCount());
    }

    public function testDelete() {
        $ebooksCategoryId = $this->getEbooksCategoryResourceId();
        $ebooksCategory = $this->resourceRepository->findOne($ebooksCategoryId);
        $countBefore = count($this->resourceRepository->findAll());
        $this->resourceRepository->delete($ebooksCategory);
        $this->getEntityManager()->flush();
        $this->assertCount($countBefore - 1, $this->resourceRepository->findAll());
        $this->assertFalse($this->resourceRepository->exists($ebooksCategoryId));
    }

    private function getEbooksCategoryResourceId(): int {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $categoryNameMetadataId = $connection->query("SELECT id FROM metadata WHERE label->'EN' = '\"Category name\"';")->fetch()['id'];
        $ebooksCategoryId = $connection
            ->query("SELECT id FROM resource WHERE contents->'{$categoryNameMetadataId}' = '[{\"value\":\"E-booki\"}]'")->fetch()['id'];
        $this->assertNotNull($ebooksCategoryId);
        return $ebooksCategoryId;
    }

    public function testGetResourcesWithPendingUpdates() {
        $this->addPendingUpdateToAllBooks();
        $resourcesWithUpdates = $this->resourceRepository->getResourcesWithPendingUpdates(50);
        foreach ($resourcesWithUpdates as $resource) {
            $this->assertNotEmpty($resource->getPendingUpdates()->toArray());
        }
    }

    private function addPendingUpdateToAllBooks() {
        $params = [
            'resourceClass' => 'books',
            'resourceKinds' => [$this->getPhpBookResource()->getKind()->getId()],
            'change' => ['metadataId' => 9, 'displayStrategy' => '{{r|mTytul}}'],
            'action' => UpdateType::OVERRIDE,
            'totalCount' => 5,
        ];
        $client = $this->createAdminClient();
        $client->apiRequest("PUT", '/api/resources', $params);
    }
}
