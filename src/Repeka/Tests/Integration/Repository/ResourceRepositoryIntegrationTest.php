<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindByResourceClassListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class ResourceRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var EntityRepository|ResourceRepository */
    private $resourceRepository;
    /** @var UserRepository */
    private $userRepository;

    public function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->loadAllFixtures();
    }

    public function testFindAll() {
        $resources = $this->resourceRepository->findAll();
        $this->assertCount(16, $resources); // resources from fixtures
    }

    public function testFindAllByBookResourceClass() {
        $query = ResourceListQuery::builder()->filterByResourceClass('books')->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(6, $paginatedResources->getResults());
        foreach ($paginatedResources->getResults() as $resource) {
            $this->assertNotEquals(SystemResourceKind::USER, $resource->getKind()->getId());
        }
    }

    public function testFindAllBookResourceClassResourceIfPageAndResultsPerPageNotSet() {
        $query = ResourceListQuery::builder()->filterByResourceClass('books')->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(6, $paginatedResources->getResults());
    }

    public function testFindFirstThreeResourcesByBookResourceClass() {
        $query = ResourceListQuery::builder()->filterByResourceClass('books')->setPage(1)->setResultsPerPage(3)->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(3, $paginatedResources->getResults());
        $this->assertEquals(6, $paginatedResources->getTotalCount());
    }

    public function testFindAllByWorkflowPlacesIdsOneId() {
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds(['qqd3yk499'])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(1, $paginatedResources->getTotalCount());
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

    public function testFindAllByWorkflowPlacesIdsManyIds() {
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds(['qqd3yk499', 'y1oosxtgf'])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(4, $paginatedResources->getTotalCount());
    }

    public function testFindDifferResultsForDifferPages() {
        $firstPageQuery = ResourceListQuery::builder()->filterByResourceClass('books')
            ->setPage(1)->setResultsPerPage(3)->build();
        $secondPageQuery = ResourceListQuery::builder()->filterByResourceClass('books')
            ->setPage(2)->setResultsPerPage(3)->build();
        $firstPaginatedResources = $this->resourceRepository->findByQuery($firstPageQuery);
        $secondPaginatedResources = $this->resourceRepository->findByQuery($secondPageQuery);
        $this->assertCount(3, $firstPaginatedResources->getResults());
        $this->assertCount(3, $secondPaginatedResources->getResults());
        $firstPageResourceIds = array_map(
            function ($resource) {
                return $resource->getId();
            },
            $firstPaginatedResources->getResults()
        );
        $secondPageResourceIds = array_map(
            function ($resource) {
                return $resource->getId();
            },
            $secondPaginatedResources->getResults()
        );
        $notInSecondPage = array_diff($firstPageResourceIds, $secondPageResourceIds);
        $this->assertCount(3, $notInSecondPage);
    }

    public function testFindAllByDictionaryResourceClass() {
        $query = ResourceListQuery::builder()->filterByResourceClass('dictionaries')->setPage(1)->setResultsPerPage(6)->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(4, $paginatedResources->getResults());
    }

    public function testFindAllByResourceKind() {
        $userResourceKind = $this->container->get(ResourceKindRepository::class)->findOne(SystemResourceKind::USER);
        $query = ResourceListQuery::builder()->filterByResourceKind($userResourceKind)->setPage(1)->setResultsPerPage(6)->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(4, $paginatedResources->getResults());
    }

    public function testFindTopLevel() {
        $paginatedTopLevelResources = $this->resourceRepository->findByQuery(
            ResourceListQuery::builder()->setPage(1)->setResultsPerPage(6)->onlyTopLevel()->build()
        );
        foreach ($paginatedTopLevelResources->getResults() as $topLevelResource) {
            $this->assertFalse($topLevelResource->hasParent());
        }
    }

    public function testFindByEmptyQuery() {
        $resources = $this->resourceRepository->findByQuery(ResourceListQuery::builder()->build());
        $this->assertCount(16, $resources);
    }

    public function testFindChildren() {
        $ebooksCategoryId = $this->getEbooksCategoryResourceId();
        $query = ResourceListQuery::builder()->filterByParentId($ebooksCategoryId)->build();
        $this->assertNotNull($ebooksCategoryId);
        $pageResult = $this->resourceRepository->findByQuery($query);
        $this->assertCount(2, $pageResult);
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

    public function testResourceCountByKind() {
        $bookResourceKind = $this->getBookResourceKind();
        $booksCount = $this->resourceRepository->countByResourceKind($bookResourceKind);
        $this->assertEquals(4, $booksCount);
    }

    public function testFindsResourcesAssignedToUser() {
        $user = $this->getBudynekUser();
        $resultsBeforeAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(0, $resultsBeforeAssigning);
        $book = $this->getPhpBookResource();
        $scannerMetadata = $this->findMetadataByName('Skanista');
        $bookContents = $book->getContents()->withReplacedValues($scannerMetadata, $user->getUserData());
        $book->updateContents($bookContents);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $resultsAfterAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning)->getId());
    }

    public function testFindsResourcesAssignedToUserByItsGroup() {
        $user = $this->getBudynekUser();
        $resultsBeforeAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(0, $resultsBeforeAssigning);
        $book = $this->getPhpBookResource();
        $scannerMetadata = $this->findMetadataByName('Skanista');
        $groups = $this->userRepository->findUserGroups($user);
        $bookContents = $book->getContents()->withReplacedValues($scannerMetadata, $groups[0]);
        $book->updateContents($bookContents);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $resultsAfterAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning)->getId());
    }

    private function getEbooksCategoryResourceId(): int {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $categoryNameMetadataId = $connection->query("SELECT id FROM metadata WHERE label->'EN' = '\"Category name\"';")->fetch()['id'];
        $ebooksCategoryId = $connection
            ->query("SELECT id FROM resource WHERE contents->'{$categoryNameMetadataId}' = '[{\"value\":\"E-booki\"}]'")->fetch()['id'];
        $this->assertNotNull($ebooksCategoryId);
        return $ebooksCategoryId;
    }

    private function getBookResourceKind(): ResourceKind {
        $resourceKindListQuery = ResourceKindListQuery::builder()->filterByResourceClass('books')->build();
        $resourceKinds = $this->handleCommand($resourceKindListQuery);
        foreach ($resourceKinds as $resourceKind) {
            /** @var ResourceKind $resourceKind */
            if ($resourceKind->getLabel()['EN'] == 'Book') {
                return $resourceKind;
            }
        }
        $this->fail("Resource kind 'Book' not found! This is a problem with the test, not the app.");
    }

    private function getBudynekUser(): UserEntity {
        /** @var UserEntity[] $users */
        $users = $this->handleCommand(new UserListQuery());
        foreach ($users as $user) {
            if ($user->getUsername() == 'budynek') {
                return $user;
            }
        }
        $this->fail("User not found");
    }
}
