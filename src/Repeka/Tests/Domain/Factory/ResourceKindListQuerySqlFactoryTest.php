<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Factory\ResourceKindListQuerySqlFactory;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;

class ResourceKindListQuerySqlFactoryTest extends \PHPUnit_Framework_TestCase {
    public function testEmptyCountQuery() {
        $query = ResourceKindListQuery::builder()->build();
        $sql = (new ResourceKindListQuerySqlFactory($query))->getTotalCountQuery();
        $this->assertContains('SELECT COUNT(id) FROM', $sql);
        $this->assertNotContains('ORDER BY', $sql);
        $this->assertNotContains('LIMIT', $sql);
    }

    public function testFilterByIds() {
        $query = ResourceKindListQuery::builder()->filterByIds([38, 44])->build();
        $factory = new ResourceKindListQuerySqlFactory($query);
        $this->assertContains('id IN', $factory->getQuery());
        $this->assertArrayHasKey('ids', $factory->getParams());
        $this->assertEquals([38, 44], $factory->getParams()['ids']);
    }

    public function testFilterByMetadataId() {
        $query = ResourceKindListQuery::builder()->filterByMetadataId(38)->build();
        $factory = new ResourceKindListQuerySqlFactory($query);
        $this->assertArrayHasKey('metadataIdFilters', $factory->getParams());
        $this->assertEquals('[{"id":38}]', $factory->getParams()['metadataIdFilters']);
    }

    public function testFilterByName() {
        $query = ResourceKindListQuery::builder()->filterByName(['PL' => 'Unicorn'])->build();
        $factory = new ResourceKindListQuerySqlFactory($query);
        $this->assertArrayHasKey('nameFilters', $factory->getParams());
        $this->assertEquals('{"PL":"Unicorn"}', $factory->getParams()['nameFilters']);
    }

    public function testFilterByResourceClasses() {
        $query = ResourceKindListQuery::builder()->filterByResourceClass('books')->build();
        $factory = new ResourceKindListQuerySqlFactory($query);
        $this->assertContains('resource_class IN', $factory->getQuery());
        $this->assertArrayHasKey('resourceClasses', $factory->getParams());
        $this->assertEquals(['books'], $factory->getParams()['resourceClasses']);
    }
}
