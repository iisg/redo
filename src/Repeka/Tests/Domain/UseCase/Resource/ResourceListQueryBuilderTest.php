<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryHandler;

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

    public function testFilteringOnlyTopLevel() {
        $this->assertFalse(ResourceListQuery::builder()->build()->onlyTopLevel());
        $this->assertTrue(ResourceListQuery::builder()->onlyTopLevel()->build()->onlyTopLevel());
    }
}
