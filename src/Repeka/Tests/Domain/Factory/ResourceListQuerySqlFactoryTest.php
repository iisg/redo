<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Factory\ResourceListQuerySqlFactory;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;

class ResourceListQuerySqlFactoryTest extends \PHPUnit_Framework_TestCase {

    public function testEmptyPageQuery() {
        $query = ResourceListQuery::builder()->build();
        $sql = (new ResourceListQuerySqlFactory($query))->getPageQuery();
        $this->assertContains('SELECT r.* FROM', $sql);
        $this->assertContains('ORDER BY', $sql);
        $this->assertNotContains('LIMIT 10 ', $sql);
    }

    public function testEmptyCountQuery() {
        $query = ResourceListQuery::builder()->build();
        $sql = (new ResourceListQuerySqlFactory($query))->getTotalCountQuery();
        $this->assertContains('SELECT COUNT(*) FROM', $sql);
        $this->assertContains('GROUP BY r.id', $sql);
        $this->assertNotContains('ORDER BY', $sql);
        $this->assertNotContains('LIMIT', $sql);
    }

    public function testPaginate() {
        $query = ResourceListQuery::builder()->setPage(3)->setResultsPerPage(10)->build();
        $sql = (new ResourceListQuerySqlFactory($query))->getPageQuery();
        $this->assertContains('LIMIT 10', $sql);
        $this->assertContains('20', $sql);
    }

    public function testFilterByIds() {
        $query = ResourceListQuery::builder()->filterByIds([38, 44])->build();
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertContains('id IN', $factory->getPageQuery());
        $this->assertArrayHasKey('ids', $factory->getParams());
        $this->assertEquals([38, 44], $factory->getParams()['ids']);
    }

    public function testFilterByResourceClasses() {
        $query = ResourceListQuery::builder()->filterByResourceClass('books')->build();
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertContains('resource_class IN', $factory->getPageQuery());
        $this->assertArrayHasKey('resourceClasses', $factory->getParams());
        $this->assertEquals(['books'], $factory->getParams()['resourceClasses']);
    }

    public function testFilterByContents() {
        $query = ResourceListQuery::builder()->filterByContents([1 => 'PHP'])->build();
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertContains('filter0', $factory->getPageQuery());
        $this->assertContains("r.contents->'1'", $factory->getPageQuery());
        $this->assertContains("~*", $factory->getPageQuery());
        $this->assertArrayHasKey('filter0', $factory->getParams());
        $this->assertEquals('PHP', $factory->getParams()['filter0']);
    }

    public function testFilterByNumber() {
        $query = ResourceListQuery::builder()->filterByContents([1 => 40])->build();
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertContains('filter0', $factory->getPageQuery());
        $this->assertContains("r.contents->'1' @> :filter0", $factory->getPageQuery());
        $this->assertNotContains("~*", $factory->getPageQuery());
        $this->assertArrayHasKey('filter0', $factory->getParams());
        $this->assertEquals('[{"value":40}]', $factory->getParams()['filter0']);
    }

    public function testFilterByAlternativeContents() {
        $query = ResourceListQuery::builder()
            ->filterByContents([5 => 'PHP'])
            ->filterByContents([6 => 'alternative'])
            ->build();
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertContains('m0', $factory->getPageQuery());
        $this->assertContains('m0', $factory->getPageQuery());
        $this->assertContains('filter0', $factory->getPageQuery());
        $this->assertContains('filter1', $factory->getPageQuery());
        $this->assertArrayHasKey('filter0', $factory->getParams());
        $this->assertArrayHasKey('filter1', $factory->getParams());
        $this->assertEquals('PHP', $factory->getParams()['filter0']);
        $this->assertEquals('alternative', $factory->getParams()['filter1']);
        $this->assertContains("r.contents->'5'", $factory->getPageQuery());
        $this->assertContains("r.contents->'6'", $factory->getPageQuery());
        $this->assertContains(' OR ', $factory->getPageQuery());
    }

    public function testFilterByAlternativeMetadataValues() {
        $query = ResourceListQuery::builder()
            ->filterByContents([5 => ['PHP', 'Python']])
            ->build();
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertContains('m0', $factory->getPageQuery());
        $this->assertNotContains('m1', $factory->getPageQuery());
        $this->assertContains('filter0', $factory->getPageQuery());
        $this->assertContains('filter1', $factory->getPageQuery());
        $this->assertArrayHasKey('filter0', $factory->getParams());
        $this->assertArrayHasKey('filter1', $factory->getParams());
        $this->assertEquals('PHP', $factory->getParams()['filter0']);
        $this->assertEquals('Python', $factory->getParams()['filter1']);
        $this->assertContains("r.contents->'5'", $factory->getPageQuery());
        $this->assertContains(' OR ', $factory->getPageQuery());
    }

    public function testFilterByNoContentsHasNoAlternatives() {
        $query = ResourceListQuery::builder()
            ->build();
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertNotContains(' OR ', $factory->getPageQuery());
    }

    public function testFilterByVisibility() {
        $query = ResourceListQuery::builder()->build();
        $unauthenticatedUser = $this->createMock(UserEntity::class);
        $unauthenticatedUser->method('getId')->willReturn(-1);
        $unauthenticatedUser->method('getGroupIdsWithUserId')->willReturn([-1]);
        EntityUtils::forceSetField($query, $unauthenticatedUser, 'executor');
        $factory = new ResourceListQuerySqlFactory($query);
        $this->assertArrayHasKey('allowedViewers', $factory->getParams());
        $this->assertEquals($factory->getParams()['allowedViewers'][0], SystemResource::UNAUTHENTICATED_USER);
        $visibilityMetadataId = SystemMetadata::VISIBILITY;
        $this->assertContains(
            "EXISTS (SELECT FROM jsonb_array_elements(COALESCE(r.contents->'$visibilityMetadataId', '[{}]')) " .
            "WHERE value->>'value' IN(:allowedViewers))",
            $factory->getPageQuery()
        );
    }
}
