<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\TreeResult;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCaseWithoutDroppingDatabase;

class ResourceRepositoryFindInTreeIntegrationTest extends IntegrationTestCaseWithoutDroppingDatabase {
    use FixtureHelpers;

    /** @var EntityRepository|ResourceRepository */
    private $resourceRepository;
    /** @var EntityRepository|ResourceKindRepository */
    private $resourceKindRepository;

    /** @var Metadata */
    private $nameMetadata;

    private static $createdResources = [];

    /** @var ResourceKind[] */
    private $resourceKinds = [];

    /*
     * Tree structure for tests: RK 'name'
     * depth:   1    2    3    4
     *          A 'top-0'
     *           +-- B '0-1'
     *           |    \-- A '0-2'
     *           |         \-- B '0-3'
     *           |
     *           +-- B '0-4'
     *           |    +-- B '0-5'
     *           |    \-- B '0-6'
     *           |
     *           \-- A '0-7'
     *
     *          B 'top-1'
     *           \-- A '1-1'
     *                \-- A '1-2'
     *                     \-- A '1-3'
     *
     *          B 'top-2'
     */
    /** @var ResourceEntity[] */
    private $resources = [];

    protected function initializeDatabaseBeforeTheFirstTest() {
        $this->nameMetadata = $this->createMetadata('named', ['PL' => 'named', 'EN' => 'named'], [], [], 'text');
        $this->resourceKinds['A'] = $this->createResourceKind(
            ['PL' => 'A', 'EN' => 'A'],
            [SystemMetadata::PARENT()->toMetadata(), $this->nameMetadata]
        );
        $this->resourceKinds['B'] = $this->createResourceKind(
            ['PL' => 'B', 'EN' => 'B'],
            [SystemMetadata::PARENT()->toMetadata(), $this->nameMetadata]
        );
        $this->prepareNamedResource('A', 'top-0');
        $this->prepareNamedResource('B', '0-1', 'top-0');
        $this->prepareNamedResource('A', '0-2', '0-1');
        $this->prepareNamedResource('B', '0-3', '0-2');
        $this->prepareNamedResource('B', '0-4', 'top-0');
        $this->prepareNamedResource('B', '0-5', '0-4');
        $this->prepareNamedResource('B', '0-6', '0-4');
        $this->prepareNamedResource('A', '0-7', 'top-0');
        $this->prepareNamedResource('B', 'top-1');
        $this->prepareNamedResource('A', '1-1', 'top-1');
        $this->prepareNamedResource('A', '1-2', '1-1');
        $this->prepareNamedResource('A', '1-3', '1-2');
        $this->prepareNamedResource('B', 'top-2');
    }

    private function prepareNamedResource($resourceKindName, $name, $parentName = null) {
        $contents = [
            $this->nameMetadata->getId() => [$name],
        ];
        if ($parentName) {
            $parentId = self::$createdResources[$parentName]->getId();
            $contents[SystemMetadata::PARENT] = $parentId;
        }
        $resourceKind = $this->resourceKinds[$resourceKindName];
        $resource = $this->createResource($resourceKind, $contents);
        self::$createdResources[$name] = $resource;
    }

    /** @before */
    public function init() {
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->resourceKindRepository = $this->container->get(ResourceKindRepository::class);
        $this->nameMetadata = $this->findMetadataByName('named');
        $this->resourceKinds['A'] = $this->resourceKindRepository->findByQuery(
            ResourceKindListQuery::builder()->filterByName(['PL' => 'A', 'EN' => 'A'])->build()
        )[0];
        $this->resourceKinds['B'] = $this->resourceKindRepository->findByQuery(
            ResourceKindListQuery::builder()->filterByName(['PL' => 'B', 'EN' => 'B'])->build()
        )[0];
        $this->resources = self::$createdResources;
    }

    public function testFindsAllResources() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(array_keys($this->resources), $tree);
        $this->assertTreeMarkedMatches(array_keys($this->resources), $tree);
    }

    public function testFindsTopLevelResources() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->includeWithinDepth(1)
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['top-0', 'top-1', 'top-2'], $tree);
        $this->assertTreeMarkedMatches(['top-0', 'top-1', 'top-2'], $tree);
    }

    public function testFindsAllMatchingResources() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->filterByResourceKind($this->resourceKinds['A'])
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['top-0', '0-1', '0-2', '0-7', 'top-1', '1-1', '1-2', '1-3'], $tree);
        $this->assertTreeMarkedMatches(['top-0', '0-2', '0-7', '1-1', '1-2', '1-3'], $tree);
    }

    public function testFindsAllResourcesWithinDepthWithDescendantsMatching() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->filterByResourceKind($this->resourceKinds['A'])
            ->includeWithinDepth(2)
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['top-0', '0-1', '0-7', 'top-1', '1-1'], $tree);
        $this->assertTreeMarkedMatches(['top-0', '0-7', '1-1'], $tree);
    }

    public function testDepthWithRootGiven() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->forRootId($this->resources['top-0']->getId())
            ->includeWithinDepth(1)
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['0-1', '0-4', '0-7'], $tree);
    }

    public function testFindsAllDescendants() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->forRootId($this->resources['top-0']->getId())
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['0-1', '0-2', '0-3', '0-4', '0-5', '0-6', '0-7'], $tree);
        $this->assertTreeMarkedMatches(['0-1', '0-2', '0-3', '0-4', '0-5', '0-6', '0-7'], $tree);
    }

    public function testPagination() {
        $rootId = $this->resources['top-0']->getId();
        $firstPage = $this->getResultsWithSingleTopLevel(1, $rootId);
        $this->assertTreeHasResources(['0-1', '0-2', '0-3'], $firstPage);
        $secondPage = $this->getResultsWithSingleTopLevel(2, $rootId);
        $this->assertTreeHasResources(['0-4', '0-5', '0-6'], $secondPage);
        $thirdPage = $this->getResultsWithSingleTopLevel(3, $rootId);
        $this->assertTreeHasResources(['0-7'], $thirdPage);
        $nonExistingPage = $this->getResultsWithSingleTopLevel(4, $rootId);
        $this->assertTreeHasResources([], $nonExistingPage);
    }

    private function getResultsWithSingleTopLevel($page, $rootId) {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->forRootId($rootId)
            ->setResultsPerPage(1)
            ->setPage($page)
            ->build();
        return $this->handleCommandBypassingFirewall($query);
    }

    public function testSiblings() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->forRootId($this->resources['top-0']->getId())
            ->setSiblings(1)
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['0-1', '0-2', '0-3', '0-4', '0-5', '0-7'], $tree);
    }

    public function testRootWithoutDescendants() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->forRootId($this->resources['top-2']->getId())
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources([], $tree);
        $this->assertTreeMarkedMatches([], $tree);
    }

    public function testRootWithoutMatchingDescendants() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->forRootId($this->resources['top-1']->getId())
            ->filterByResourceKind($this->resourceKinds['B'])
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources([], $tree);
        $this->assertTreeMarkedMatches([], $tree);
    }

    public function testUsesRegex() {
        $query = ResourceTreeQuery::builder()
            ->filterByResourceClass('books')
            ->filterByContents([$this->nameMetadata->getId() => '0-[2|4]'])
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['top-0', '0-1', '0-2', '0-4'], $tree);
        $this->assertTreeMarkedMatches(['0-2', '0-4'], $tree);
    }

    public function testOneMoreElements() {
        $query = ResourceTreeQuery::builder()
            ->oneMoreElements()
            ->filterByResourceClass('books')
            ->forRootId($this->resources['top-0']->getId())
            ->includeWithinDepth(2)
            ->setSiblings(1)
            ->setResultsPerPage(1)
            ->setPage(1)
            ->build();
        $tree = $this->handleCommandBypassingFirewall($query);
        $this->assertTreeHasResources(['0-1', '0-2', '0-4', '0-5', '0-6'], $tree);
        $this->assertTreeMarkedMatches(['0-1', '0-2', '0-4', '0-5', '0-6'], $tree);
    }

    /**
     * @param $expectedResourceNames string[]
     * @param $actualTree TreeResult
     */
    private function assertTreeHasResources($expectedResourceNames, $actualTree) {
        $this->assertCount(count($expectedResourceNames), $actualTree->getTreeContents());
        $actualIds = EntityUtils::mapToIds($actualTree->getTreeContents());
        foreach ($expectedResourceNames as $resourceName) {
            $resourceId = $this->resources[$resourceName]->getId();
            $this->assertContains($resourceId, $actualIds);
        }
    }

    /**
     * @param $expectedResourceNames string[]
     * @param $actualTree TreeResult
     */
    private function assertTreeMarkedMatches($expectedResourceNames, $actualTree) {
        $this->assertCount(count($expectedResourceNames), $actualTree->getMatchingIds());
        $expectedResources = array_intersect_key(
            $this->resources,
            array_flip($expectedResourceNames)
        );
        $expectedIds = EntityUtils::mapToIds($expectedResources);
        $this->assertArrayHasAllValues($expectedIds, $actualTree->getMatchingIds());
    }
}
