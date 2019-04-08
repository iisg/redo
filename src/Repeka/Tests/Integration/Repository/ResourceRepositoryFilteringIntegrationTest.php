<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @small
 */
class ResourceRepositoryFilteringIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var EntityRepository|ResourceRepository */
    private $resourceRepository;
    /** @var UserRepository */
    private $userRepository;
    /** @var Metadata */
    private $titleMetadata;
    /** @var UserEntity */
    private $admin;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    /** @before */
    public function init() {
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->titleMetadata = $this->findMetadataByName('Tytuł');
        $this->admin = $this->getAdminUser();
    }

    public function testResourcesFilteredByVisibilityWhenExecutorIsSet() {
        $query = ResourceListQuery::builder()->build();
        $unauthenticatedUser = $this->getUnauthenticatedUser();
        EntityUtils::forceSetField($query, $unauthenticatedUser, 'executor');
        $resourcesIds = EntityUtils::mapToIds($this->resourceRepository->findByQuery($query)->getResults());
        $this->assertCount(6, $resourcesIds);
        $this->assertNotContains(1, $resourcesIds);
        $this->assertNotContains(5, $resourcesIds);
        $this->assertNotContains(6, $resourcesIds);
        $this->assertNotContains(13, $resourcesIds);
    }

    public function testFindAll() {
        $resources = $this->resourceRepository->findAll();
        $this->assertCount($this->resourceRepository->count([]), $resources);
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

    public function testSortingIncludesResourcesWithoutMetadata() {
        $titleMetadataId = $this->titleMetadata->getId();
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('books')
            ->sortBy([['columnId' => $titleMetadataId, 'direction' => 'ASC']])
            ->build();
        $resources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(6, $resources->getResults());
    }

    public function testSortingAscendingPutsResourcesWithoutMetadataAtEnd() {
        $titleMetadataId = $this->titleMetadata->getId();
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('books')
            ->sortBy([['columnId' => $titleMetadataId, 'direction' => 'ASC']])
            ->build();
        /** @var ResourceEntity[] $resources */
        $resources = $this->resourceRepository->findByQuery($query)->getResults();
        $lastWithMetadata = $resources[4];
        $firstWithoutMetadata = $resources[5];
        $this->assertArrayHasKey($titleMetadataId, $lastWithMetadata->getContents());
        $this->assertArrayNotHasKey($titleMetadataId, $firstWithoutMetadata->getContents());
    }

    public function testSortingDescendingPutsResourcesWithoutMetadataAtBeginning() {
        $titleMetadataId = $this->titleMetadata->getId();
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('books')
            ->sortBy([['columnId' => $titleMetadataId, 'direction' => 'DESC']])
            ->build();
        /** @var ResourceEntity[] $resources */
        $resources = $this->resourceRepository->findByQuery($query)->getResults();
        $lastWithoutMetadata = $resources[0];
        $firstWithMetadata = $resources[1];
        $this->assertArrayNotHasKey($titleMetadataId, $lastWithoutMetadata->getContents());
        $this->assertArrayHasKey($titleMetadataId, $firstWithMetadata->getContents());
    }

    public function testFindAllByDictionaryResourceClass() {
        $query = ResourceListQuery::builder()->filterByResourceClass('dictionaries')->setPage(1)->setResultsPerPage(6)->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(6, $paginatedResources->getResults());
    }

    public function testFindAllByResourceKind() {
        $userResourceKind = $this->container->get(ResourceKindRepository::class)->findOne(SystemResourceKind::USER);
        $query = ResourceListQuery::builder()->filterByResourceKind($userResourceKind)->setPage(1)->setResultsPerPage(6)->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertCount(5, $paginatedResources->getResults());
    }

    public function testFindTopLevel() {
        $query = ResourceListQuery::builder()->setPage(1)->setResultsPerPage(6)->onlyTopLevel()->build();
        $paginatedTopLevelResources = $this->resourceRepository->findByQuery($query);
        foreach ($paginatedTopLevelResources->getResults() as $topLevelResource) {
            $this->assertFalse($topLevelResource->hasParent());
        }
    }

    public function testFindByEmptyQuery() {
        $query = ResourceListQuery::builder()->build();
        $resources = $this->resourceRepository->findByQuery($query);
        $this->assertCount($this->resourceRepository->count([]), $resources);
    }

    public function testFindChildren() {
        $ebooksCategoryId = $this->getEbooksCategoryResourceId();
        $query = ResourceListQuery::builder()->filterByParentId($ebooksCategoryId)->build();
        $this->assertNotNull($ebooksCategoryId);
        $pageResult = $this->resourceRepository->findByQuery($query);
        $this->assertCount(2, $pageResult);
    }

    public function testResourceCountByKind() {
        $bookResourceKind = $this->getBookResourceKind();
        $booksCount = $this->resourceRepository->countByResourceKind($bookResourceKind);
        $this->assertEquals(4, $booksCount);
    }

    public function testFindUsersInGroup() {
        $skanisci = $this->findResourceByContents([SystemMetadata::USERNAME => 'Skaniści']);
        $users = $this->resourceRepository->findUsersInGroup($skanisci);
        $this->assertCount(3, $users);
        $this->assertContains($this->getAdminUser()->getUserData(), $users);
    }

    public function testCheckingIfChildrenExist() {
        $ebooks = $this->findResourceByContents(['nazwaKategorii' => 'E-booki']);
        $this->assertTrue($this->resourceRepository->hasChildren($ebooks));
        $this->assertFalse($this->resourceRepository->hasChildren($this->getPhpBookResource()));
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
        $resourceKinds = $this->handleCommandBypassingFirewall($resourceKindListQuery);
        foreach ($resourceKinds as $resourceKind) {
            /** @var ResourceKind $resourceKind */
            if ($resourceKind->getLabel()['EN'] == 'Book') {
                return $resourceKind;
            }
        }
        $this->fail("Resource kind 'Book' not found! This is a problem with the test, not the app.");
    }
}
