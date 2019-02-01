<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Factory\MetadataListQuerySqlFactory;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;

class MetadataListQuerySqlFactoryTest extends \PHPUnit_Framework_TestCase {
    public function testEmptyPageQuery() {
        $query = MetadataListQuery::builder()->build();
        $sql = (new MetadataListQuerySqlFactory($query))->getPageQuery();
        $this->assertContains('SELECT m.* FROM', $sql);
        $this->assertContains('ORDER BY', $sql);
        $this->assertNotContains('LIMIT', $sql);
    }

    public function testEmptyCountQuery() {
        $query = MetadataListQuery::builder()->build();
        $sql = (new MetadataListQuerySqlFactory($query))->getTotalCountQuery();
        $this->assertContains('SELECT COUNT(id) FROM', $sql);
        $this->assertNotContains('ORDER BY', $sql);
        $this->assertNotContains('LIMIT', $sql);
    }

    public function testFilterByIds() {
        $query = MetadataListQuery::builder()->filterByIds([38, 44])->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains('id IN', $factory->getPageQuery());
        $this->assertArrayHasKey('ids', $factory->getParams());
        $this->assertEquals([38, 44], $factory->getParams()['ids']);
    }

    public function testFilterByResourceClasses() {
        $query = MetadataListQuery::builder()->filterByResourceClass('books')->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains('resource_class IN', $factory->getPageQuery());
        $this->assertArrayHasKey('resourceClasses', $factory->getParams());
        $this->assertEquals(['books'], $factory->getParams()['resourceClasses']);
    }

    public function testFilterByNames() {
        $query = MetadataListQuery::builder()->filterByNames(['Tytuł', 'Opis'])->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains('name IN', $factory->getPageQuery());
        $this->assertArrayHasKey('metadataNames', $factory->getParams());
        $this->assertEquals(['tytul', 'opis'], $factory->getParams()['metadataNames']);
    }

    public function testFilterByControls() {
        $query = MetadataListQuery::builder()->filterByControls([MetadataControl::FILE(), MetadataControl::TEXT()])->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains('control IN', $factory->getPageQuery());
        $this->assertArrayHasKey('controlNames', $factory->getParams());
        $this->assertEquals(['file', 'text'], $factory->getParams()['controlNames']);
    }

    public function testFilterByTopLevel() {
        $query = MetadataListQuery::builder()->onlyTopLevel()->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains('parent_id is null', $factory->getPageQuery());
    }

    public function testFilterByParentMetadata() {
        //Given metadata is only for test needs
        $query = MetadataListQuery::builder()->filterByParent(SystemMetadata::USERNAME()->toMetadata())->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains('parent_id =', $factory->getPageQuery());
        $this->assertArrayHasKey('parentId', $factory->getParams());
        $this->assertEquals(-2, $factory->getParams()['parentId']);
    }

    public function testAddSystemIds() {
        $query = MetadataListQuery::builder()->addSystemMetadataIds([-1, -2])->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains('id IN', $factory->getPageQuery());
        $this->assertArrayHasKey('systemMetadataIds', $factory->getParams());
        $this->assertEquals([-1, -2], $factory->getParams()['systemMetadataIds']);
    }

    public function testFilterByRequiredResourceKind() {
        $query = MetadataListQuery::builder()->filterByRequiredKindIds([2])->build();
        $factory = new MetadataListQuerySqlFactory($query);
        $this->assertContains("((constraints->>'resourceKind') is NULL OR (constraints->'resourceKind') @>", $factory->getPageQuery());
        $this->assertArrayHasKey('requiredKindId', $factory->getParams());
        $this->assertEquals(2, $factory->getParams()['requiredKindId']);
    }
}
