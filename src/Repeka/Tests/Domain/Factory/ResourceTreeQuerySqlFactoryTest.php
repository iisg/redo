<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;

class ResourceTreeQuerySqlFactoryTest extends \PHPUnit_Framework_TestCase {
    public function testEmptyTreeQuery() {
        $query = ResourceTreeQuery::builder()
            ->build();
        $factory =  (new ResourceTreeQuerySqlFactory($query));
        $sql = $factory->getTreeQuery();
        $this->assertContains('SELECT r.*', $sql);
        $this->assertContains('WHERE ancestors.next_ancestor_id IS NULL', $sql);
        $this->assertContains('[1 : 10000]', $sql);
        $this->assertContains('node_info.depth = 1 OR node_info.depth >= 2', $sql);
    }

    public function testTreeQueryWithRoot() {
        $query = ResourceTreeQuery::builder()
            ->forRootId(123)
            ->build();
        $factory =  (new ResourceTreeQuerySqlFactory($query));
        $sql = $factory->getTreeQuery();
        $this->assertArrayHasEntry('root', '123', $factory->getParams());
        $this->assertContains('WHERE ancestors.list [1] = :root', $sql);
        $this->assertContains('[2 : 10000]', $sql);
        $this->assertContains('node_info.depth = 1 OR node_info.depth >= 2', $sql);
    }

    public function testTreeQueryWithDepth() {
        $query = ResourceTreeQuery::builder()
            ->includeWithinDepth(40)
            ->build();
        $factory =  (new ResourceTreeQuerySqlFactory($query));
        $sql = $factory->getTreeQuery();
        $this->assertContains('WHERE ancestors.next_ancestor_id IS NULL', $sql);
        $this->assertContains('[1 : 40]', $sql);
        $this->assertContains('node_info.depth = 1 OR node_info.depth >= 2', $sql);
    }

    public function testTreeQueryWithDepthAndRoot() {
        $query = ResourceTreeQuery::builder()
            ->includeWithinDepth(40)
            ->forRootId(123)
            ->build();
        $factory =  (new ResourceTreeQuerySqlFactory($query));
        $sql = $factory->getTreeQuery();
        $this->assertArrayHasEntry('root', '123', $factory->getParams());
        $this->assertContains('WHERE ancestors.list [1] = :root', $sql);
        $this->assertContains('[2 : 41]', $sql);
        $this->assertContains('node_info.depth = 1 OR node_info.depth >= 2', $sql);
    }

    public function testTreeQueryWithSiblingsAndPagination() {
        $query = ResourceTreeQuery::builder()
            ->setSiblings(30)
            ->setPage(2)
            ->setResultsPerPage(5)
            ->build();
        $factory =  (new ResourceTreeQuerySqlFactory($query));
        $sql = $factory->getTreeQuery();
        $this->assertArrayHasEntry('pageStart', '6', $factory->getParams());
        $this->assertArrayHasEntry('pageEnd', '11', $factory->getParams());
        $this->assertArrayHasEntry('maxSiblings', '30', $factory->getParams());
        $this->assertContains('WHERE ancestors.next_ancestor_id IS NULL', $sql);
        $this->assertContains('[1 : 10000]', $sql);
        $this->assertContains('node_info.depth = 1 AND node_info.row >= :pageStart AND node_info.row < :pageEnd', $sql);
        $this->assertContains('node_info.depth >= 2 AND node_info.row <= :maxSiblings', $sql);
    }

    public function testTreeQueryWithSiblingsAndPaginationPlusOneMoreElement() {
        $query = ResourceTreeQuery::builder()
            ->oneMoreElements()
            ->setSiblings(30)
            ->setPage(2)
            ->setResultsPerPage(5)
            ->build();
        $factory =  (new ResourceTreeQuerySqlFactory($query));
        $sql = $factory->getTreeQuery();
        $this->assertArrayHasEntry('pageStart', '6', $factory->getParams());
        $this->assertArrayHasEntry('pageEnd', '12', $factory->getParams());
        $this->assertArrayHasEntry('maxSiblings', '31', $factory->getParams());
        $this->assertContains('WHERE ancestors.next_ancestor_id IS NULL', $sql);
        $this->assertContains('[1 : 10000]', $sql);
        $this->assertContains('node_info.depth = 1 AND node_info.row >= :pageStart AND node_info.row < :pageEnd', $sql);
        $this->assertContains('node_info.depth >= 2 AND node_info.row <= :maxSiblings', $sql);
    }

    public function testMatchingResourcesQuery() {
        $query = ResourceTreeQuery::builder()
            ->build();
        $factory =  (new ResourceTreeQuerySqlFactory($query));
        $sql = $factory->getMatchingResourcesQuery([1,2,500]);
        $this->assertContains('SELECT r.id AS id FROM', $sql);
        $this->assertArrayHasEntry('res1', 1, $factory->getParams());
        $this->assertArrayHasEntry('res2', 2, $factory->getParams());
        $this->assertArrayHasEntry('res500', 500, $factory->getParams());
        $this->assertContains('AND id IN (:res1, :res2, :res500)', $sql);
    }

    private function assertArrayHasEntry($key, $value, array $array) {
        $this->assertArrayHasKey($key, $array);
        $actualValue = $array[$key];
        $this->assertEquals($value, $actualValue, "Expected that key ${key} has value of ${value}, but has ${actualValue}");
    }
}
