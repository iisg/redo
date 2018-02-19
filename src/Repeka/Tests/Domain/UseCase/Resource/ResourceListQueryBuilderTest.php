<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

class ResourceListQueryBuilderTest extends \PHPUnit_Framework_TestCase {
    public function testResourceClassFilter() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClasses(['a', 'b'])
            ->build();
        $this->assertEquals(['a', 'b'], $query->getResourceClasses());
    }

    public function testResourceClassFilterIsAdditive() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClasses(['a', 'b'])
            ->filterByResourceClasses(['c', 'd'])
            ->filterByResourceClass('e')
            ->build();
        $this->assertEquals(['a', 'b', 'c', 'd', 'e'], $query->getResourceClasses());
    }

    public function testResourceClassFilterIsUnique() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('a')
            ->filterByResourceClass('a')
            ->build();
        $this->assertEquals(['a'], $query->getResourceClasses());
    }

    public function testResourceKindsFilter() {
        $kinds = [$this->createMock(ResourceKind::class), $this->createMock(ResourceKind::class)];
        $query = ResourceListQuery::builder()
            ->filterByResourceKinds($kinds)
            ->build();
        $this->assertEquals($kinds, $query->getResourceKinds());
    }

    public function testResourceKindsFilterIsAdditive() {
        $kind1 = $this->createMock(ResourceKind::class);
        $kind2 = $this->createMock(ResourceKind::class);
        $query = ResourceListQuery::builder()
            ->filterByResourceKind($kind1)
            ->filterByResourceKind($kind2)
            ->build();
        $this->assertEquals([$kind1, $kind2], $query->getResourceKinds());
    }

    public function testSettingParentId() {
        $parentId = 1;
        $query = ResourceListQuery::builder()->filterByParentId($parentId)->build();
        $this->assertEquals($parentId, $query->getParentId());
    }

    public function testSettingPage() {
        $page = 4;
        $query = ResourceListQuery::builder()->setPage($page)->build();
        $this->assertEquals($page, $query->getPage());
    }

    public function testSettingResultsPerPage() {
        $resultsPerPage = 4;
        $query = ResourceListQuery::builder()->setResultsPerPage($resultsPerPage)->build();
        $this->assertEquals($resultsPerPage, $query->getResultsPerPage());
    }

    public function testFilteringOnlyTopLevel() {
        $this->assertFalse(ResourceListQuery::builder()->build()->onlyTopLevel());
        $this->assertTrue(ResourceListQuery::builder()->onlyTopLevel()->build()->onlyTopLevel());
    }

    public function testPaginationReturnTrueIfPageSet() {
        $this->assertTrue(ResourceListQuery::builder()->setPage(1)->setResultsPerPage(10)->build()->paginate());
    }

    public function testPaginationReturnFalseIfPageSet() {
        $this->assertFalse(ResourceListQuery::builder()->build()->paginate());
    }

    public function testFilteringByResourceContents() {
        $contents = [1 => 'test'];
        $query = ResourceListQuery::builder()->filterByContents([1 => 'test'])->build();
        $this->assertEquals(ResourceContents::fromArray($contents), $query->getContentsFilter());
    }

    public function testSortByMetadataId() {
        $sortBy = [2 => 'ASC'];
        $query = ResourceListQuery::builder()->sortByMetadataIds($sortBy)->build();
        $this->assertEquals($sortBy, $query->getSortByMetadataIds());
    }
}
