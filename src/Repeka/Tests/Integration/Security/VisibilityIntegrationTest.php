<?php
namespace Repeka\Tests\Integration\Security;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/**
 * All tests in this class rely heavily on
 * fixtures and current roles configuration
 * @small
 */
class VisibilityIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $resourceKind;
    /** @var UserEntity */
    private $admin;
    /** @var UserEntity */
    private $budynek;
    /** @var UserEntity */
    private $skaner;
    /** @var ResourceEntity */
    private $invisibleBook;
    /** @var ResourceEntity */
    private $invisibleDictionary;
    /** @var ResourceEntity */
    private $resourceVisibleOnlyInTeaser;
    /** @var ResourceEntity */
    private $resourceVisibleBySkanerGroup;
    /** @var ResourceEntity */
    private $resourceVisibleForEverybody;
    /** @var ResourceEntity */
    private $parentResource;
    /** @var Metadata */
    private $titleMetadata;
    /** @var Metadata */
    private $nameMetadata;

    const RESOURCES_ENDPOINT = '/api/resources';
    const TREE_ENDPOINT = '/api/resources/tree';
    const SINGLE_RESOURCE_ENDPOINT = '/api/resources/%d';

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->executeCommand('repeka:fts:initialize -e test');
        $this->admin = $this->getAdminUser();
        $this->budynek = $this->getBudynekUser();
        $this->skaner = $this->getSkanerUser();
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $this->titleMetadata = $metadataRepository->findByName('tytul');
        $this->nameMetadata = $metadataRepository->findByName('nazwa');
        $dictionaryResourceKind = $this->createResourceKind('dict', ['PL' => 'slownik', 'EN' => 'dict'], [$this->nameMetadata]);
        $this->resourceKind = $this->createResourceKind('testrk', ['PL' => 'rodzaj', 'EN' => 'kind'], [$this->titleMetadata]);
        $this->addSupportForResourceKindToMetadata(SystemMetadata::VISIBILITY, $this->resourceKind->getId());
        $this->addSupportForResourceKindToMetadata(SystemMetadata::TEASER_VISIBILITY, $this->resourceKind->getId());
        $this->addSupportForResourceKindToMetadata(SystemMetadata::VISIBILITY, $dictionaryResourceKind->getId());
        $this->addSupportForResourceKindToMetadata(SystemMetadata::TEASER_VISIBILITY, $dictionaryResourceKind->getId());
        $this->parentResource = $this->createResource(
            $this->resourceKind,
            [
                $this->titleMetadata->getId() => ['Parent visible in teaser'],
                SystemMetadata::VISIBILITY => [],
                SystemMetadata::TEASER_VISIBILITY => [1, 5, 6, SystemResource::UNAUTHENTICATED_USER],
            ]
        );
        $this->invisibleBook = $this->createResource(
            $this->resourceKind,
            [
                $this->titleMetadata->getId() => ['Not visible at all'],
                SystemMetadata::PARENT => [$this->parentResource->getId()],
                SystemMetadata::VISIBILITY => [],
                SystemMetadata::TEASER_VISIBILITY => [],
            ]
        );
        $this->invisibleDictionary = $this->createResource(
            $dictionaryResourceKind,
            [
                $this->nameMetadata->getId() => ['Not visible dictionary'],
                SystemMetadata::VISIBILITY => [],
                SystemMetadata::TEASER_VISIBILITY => [],
            ]
        );
        $this->resourceVisibleOnlyInTeaser = $this->createResource(
            $this->resourceKind,
            [
                $this->titleMetadata->getId() => ['Visible only in relationship'],
                SystemMetadata::VISIBILITY => [],
                SystemMetadata::PARENT => [$this->parentResource->getId()],
                SystemMetadata::TEASER_VISIBILITY => [1, 5, 6, SystemResource::UNAUTHENTICATED_USER],
            ]
        );
        $this->resourceVisibleBySkanerGroup = $this->createResource(
            $this->resourceKind,
            [
                $this->titleMetadata->getId() => ['Visible only by skaner group'],
                SystemMetadata::VISIBILITY => [6],
                SystemMetadata::TEASER_VISIBILITY => [6],
            ]
        );
        $this->resourceVisibleForEverybody = $this->createResource(
            $this->resourceKind,
            [
                $this->titleMetadata->getId() => ['Visible for everyone'],
                SystemMetadata::PARENT => [$this->parentResource->getId()],
                SystemMetadata::VISIBILITY => [1, 5, 6, SystemResource::UNAUTHENTICATED_USER],
                SystemMetadata::TEASER_VISIBILITY => [1, 5, 6, SystemResource::UNAUTHENTICATED_USER],
            ]
        );
    }

    public function testUnauthenticatedUserSeesOnlyPublicResourcesInList() {
        $client = $this->createClient();
        $client->apiRequest('GET', self::RESOURCES_ENDPOINT, [], []);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertCount(7, $fetchedIds);
        $this->assertContains($this->getPhpBookResource()->getId(), $fetchedIds);
        $this->assertNotContains(1, $fetchedIds);
        $this->assertNotContains($this->resourceVisibleBySkanerGroup->getId(), $fetchedIds);
        $this->assertNotContains($this->resourceVisibleOnlyInTeaser->getId(), $fetchedIds);
        $this->assertNotContains($this->invisibleBook->getId(), $fetchedIds);
    }

    public function testUnauthenticatedUserSeesOnlyPublicResourcesInESResults() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('visible')
            ->setPage(1)
            ->setResultsPerPage(5)
            ->setSearchableMetadata([$this->titleMetadata->getId()])
            ->build();
        $unauthenticatedUser = $this->getUnauthenticatedUser();
        $resources = $this->handleCommandAs($unauthenticatedUser, $query);
        $ids = EntityUtils::mapToIds($resources);
        $this->assertCount(1, $resources);
        $this->assertContains($this->resourceVisibleForEverybody->getId(), $ids);
    }

    public function testBudynekUserSeesResourcesVisibleForHisGroupInList() {
        $client = $this->createAuthenticatedClient($this->budynek->getUsername(), 'budynek');
        $client->apiRequest('GET', self::RESOURCES_ENDPOINT, [], []);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertCount(16, $fetchedIds, $client->getResponse()->getContent());
        $this->assertContains($this->resourceVisibleBySkanerGroup->getId(), $fetchedIds);
        $this->assertNotContains($this->resourceVisibleOnlyInTeaser->getId(), $fetchedIds);
        $this->assertNotContains($this->invisibleBook->getId(), $fetchedIds);
    }

    public function testBudynekUserSeesResourcesVisibleForHisGroupInESResults() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('visible')
            ->setPage(1)
            ->setResultsPerPage(5)
            ->setSearchableMetadata([$this->titleMetadata->getId()])
            ->build();
        $resources = $this->handleCommandAs($this->budynek, $query);
        $ids = EntityUtils::mapToIds($resources);
        $this->assertCount(2, $resources);
        $this->assertContains($this->resourceVisibleForEverybody->getId(), $ids);
        $this->assertContains($this->resourceVisibleBySkanerGroup->getId(), $ids);
    }

    public function testAdminCanSeeAllResourcesInList() {
        $client = $this->createAdminClient();
        $client->apiRequest('GET', self::RESOURCES_ENDPOINT, [], []);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertContains($this->invisibleBook->getId(), $fetchedIds);
        $this->assertContains($this->invisibleDictionary->getId(), $fetchedIds);
        $this->assertContains($this->resourceVisibleOnlyInTeaser->getId(), $fetchedIds);
    }

    public function testAdminCanSeeAllResourcesInTree() {
        $client = $this->createAdminClient();
        $client->apiRequest('GET', self::TREE_ENDPOINT, [], []);
        $this->assertStatusCode(200, $client->getResponse());
        $treeResult = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('treeContents', $treeResult);
        $resources = $treeResult['treeContents'];
        $fetchedIds = array_column($resources, 'id');
        $this->assertContains($this->invisibleBook->getId(), $fetchedIds);
        $this->assertContains($this->invisibleDictionary->getId(), $fetchedIds);
        $this->assertContains($this->resourceVisibleOnlyInTeaser->getId(), $fetchedIds);
    }

    public function testNoOneSeesInvisibleResourceInESResults() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('visible')
            ->setPage(1)
            ->setResultsPerPage(5)
            ->setSearchableMetadata([$this->titleMetadata->getId()])
            ->build();
        $resources = $this->handleCommandAs($this->admin, $query);
        $ids = EntityUtils::mapToIds($resources);
        $this->assertCount(2, $resources);
        $this->assertNotContains($this->invisibleBook->getId(), $ids);
        $this->assertNotContains($this->invisibleDictionary->getId(), $ids);
        $this->assertNotContains($this->resourceVisibleOnlyInTeaser->getId(), $ids);
    }

    public function testClassAdminCanFetchSingleInvisibleResource() {
        $client = $this->createAdminClient();
        $client->apiRequest('GET', $this->singleResourceEndpoint($this->invisibleBook->getId()));
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testFetchingSingleResourceInvisibleForAdminOfOtherClassIsForbidden() {
        $client = $this->createAuthenticatedClient($this->skaner->getUsername(), 'skaner');
        $client->apiRequest('GET', $this->singleResourceEndpoint($this->invisibleBook->getId()));
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testFetchingSingleResourceInvisibleForCurrentUserIsForbidden() {
        $client = $this->createAuthenticatedClient($this->budynek->getUsername(), 'budynek');
        $client->apiRequest('GET', $this->singleResourceEndpoint($this->invisibleBook->getId()));
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testFetchingSingleResourceVisibleOnlyInTeaserIsForbidden() {
        $client = $this->createAuthenticatedClient($this->budynek->getUsername(), 'budynek');
        $client->apiRequest('GET', $this->singleResourceEndpoint($this->resourceVisibleOnlyInTeaser->getId()));
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testTreeVisibilityForBudynekUser() {
        $client = $this->createAuthenticatedClient($this->budynek->getUsername(), 'budynek');
        $client->apiRequest('GET', self::TREE_ENDPOINT, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
        $treeResult = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('treeContents', $treeResult);
        $resources = $treeResult['treeContents'];
        $ids = array_column($resources, 'id');
        $this->assertContains($this->resourceVisibleOnlyInTeaser->getId(), $ids);
        $this->assertContains($this->resourceVisibleForEverybody->getId(), $ids);
        $this->assertContains($this->parentResource->getId(), $ids);
        $this->assertNotContains($this->invisibleBook->getId(), $ids);
        foreach ($resources as $resource) {
            $this->assertArrayHasKey('contents', $resource);
            $contents = $resource['contents'];
            $this->assertCount(2, $contents);
            $this->assertArrayHasKey(SystemMetadata::PARENT, $contents);
            $this->assertArrayHasKey(SystemMetadata::RESOURCE_LABEL, $contents);
            $this->assertArrayHasKey('isTeaser', $resource);
            $this->assertTrue($resource['isTeaser']);
            $this->assertArrayHasKey('canView', $resource);
        }
    }

    public function testFetchingHierarchyFromOtherResourceClassForbiddenForAdminSomeClass() {
        $client = $this->createAuthenticatedClient($this->budynek->getUsername(), 'budynek');
        $client->apiRequest('GET', $this->hierarchyEndpoint($this->resourceVisibleOnlyInTeaser->getId()));
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testFetchingHierarchyAlwaysAllowedForAdmin() {
        $client = $this->createAdminClient();
        $client->apiRequest('GET', $this->hierarchyEndpoint($this->invisibleBook->getId()));
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                [
                    'id' => $this->parentResource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'resourceClass' => $this->resourceKind->getResourceClass(),
                    'displayStrategiesDirty' => false,
                    'hasChildren' => true,
                    'isTeaser' => true,
                    'canView' => true,
                    'contents' => ResourceContents::fromArray(
                        [
                            SystemMetadata::RESOURCE_LABEL => ['#' . $this->parentResource->getId()],
                            SystemMetadata::PARENT => [],
                        ]
                    )->toArray(),
                ],
            ],
            $client->getResponse()->getContent()
        );
    }

    public function testFetchingHierarchyOfInvisibleResourceForBudynekUserForbidden() {
        $client = $this->createAuthenticatedClient($this->budynek->getUsername(), 'budynek');
        $client->apiRequest('GET', $this->hierarchyEndpoint($this->invisibleBook->getId()));
        $this->assertStatusCode(403, $client->getResponse());
    }

    public function testFetchingResourceHierarchyForBudynekUser() {
        $client = $this->createAuthenticatedClient($this->budynek->getUsername(), 'budynek');
        $client->apiRequest('GET', $this->hierarchyEndpoint($this->resourceVisibleForEverybody->getId()));
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                [
                    'id' => $this->parentResource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'resourceClass' => $this->resourceKind->getResourceClass(),
                    'displayStrategiesDirty' => false,
                    'hasChildren' => true,
                    'isTeaser' => true,
                    'canView' => false,
                    'contents' => ResourceContents::fromArray(
                        [
                            SystemMetadata::RESOURCE_LABEL => ['#' . $this->parentResource->getId()],
                            SystemMetadata::PARENT => [],
                        ]
                    )->toArray(),
                ],
            ],
            $client->getResponse()->getContent()
        );
    }

    public function testResourceClassAdminSeesAllResourcesInTree() {
        $client = $this->createAdminClient();
        $client->apiRequest('GET', self::TREE_ENDPOINT, [], []);
        $this->assertStatusCode(200, $client->getResponse());
        $treeResult = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('treeContents', $treeResult);
        $resources = $treeResult['treeContents'];
        $ids = array_column($resources, 'id');
        $this->assertContains($this->resourceVisibleOnlyInTeaser->getId(), $ids);
        $this->assertContains($this->invisibleBook->getId(), $ids);
        $this->assertContains($this->invisibleDictionary->getId(), $ids);
    }

    public function testAdminOfOtherClassDoesNotSeeAllResourcesInList() {
        $client = $this->createAuthenticatedClient($this->skaner->getUsername(), 'skaner');
        $client->apiRequest('GET', self::RESOURCES_ENDPOINT, [], []);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertCount(17, $fetchedIds);
        $this->assertContains($this->resourceVisibleBySkanerGroup->getId(), $fetchedIds);
        $this->assertContains($this->invisibleDictionary->getId(), $fetchedIds);
        $this->assertNotContains($this->resourceVisibleOnlyInTeaser->getId(), $fetchedIds);
        $this->assertNotContains($this->invisibleBook->getId(), $fetchedIds);
    }

    public function testAdminOfOtherClassDoesNotSeeAllResourcesInTree() {
        $client = $this->createAuthenticatedClient($this->skaner->getUsername(), 'skaner');
        $client->apiRequest('GET', self::TREE_ENDPOINT, [], []);
        $this->assertStatusCode(200, $client->getResponse());
        $treeResult = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('treeContents', $treeResult);
        $resources = $treeResult['treeContents'];
        $ids = array_column($resources, 'id');
        $this->assertContains($this->resourceVisibleOnlyInTeaser->getId(), $ids);
        $this->assertContains($this->resourceVisibleForEverybody->getId(), $ids);
        $this->assertContains($this->parentResource->getId(), $ids);
        $this->assertContains($this->invisibleDictionary->getId(), $ids);
        $this->assertNotContains($this->invisibleBook->getId(), $ids);
        foreach ($resources as $resource) {
            $this->assertArrayHasKey('contents', $resource);
            $contents = $resource['contents'];
            $this->assertLessThanOrEqual(3, count($contents));
            $this->assertArrayHasKey(SystemMetadata::PARENT, $contents);
            $this->assertArrayHasKey(SystemMetadata::RESOURCE_LABEL, $contents);
            $this->assertArrayHasKey('isTeaser', $resource);
            $this->assertTrue($resource['isTeaser']);
            $this->assertArrayHasKey('canView', $resource);
        }
    }

    private function singleResourceEndpoint(int $id) {
        return sprintf(self::SINGLE_RESOURCE_ENDPOINT, $id);
    }

    private function hierarchyEndpoint(int $id) {
        return sprintf(self::SINGLE_RESOURCE_ENDPOINT, $id) . '/hierarchy';
    }
}
