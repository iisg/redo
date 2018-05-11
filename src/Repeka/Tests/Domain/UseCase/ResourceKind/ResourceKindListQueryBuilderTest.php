<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;

class ResourceKindListQueryBuilderTest extends \PHPUnit_Framework_TestCase {

    public function testResourceClassFilter() {
        $query = ResourceKindListQuery::builder()
            ->filterByResourceClasses(['a', 'b'])
            ->build();
        $this->assertEquals(['a', 'b'], $query->getResourceClasses());
    }

    public function testResourceClassFilterIsAdditive() {
        $query = ResourceKindListQuery::builder()
            ->filterByResourceClasses(['a', 'b'])
            ->filterByResourceClasses(['c', 'd'])
            ->filterByResourceClass('e')
            ->build();
        $this->assertEquals(['a', 'b', 'c', 'd', 'e'], $query->getResourceClasses());
    }

    public function testResourceClassFilterIsUnique() {
        $query = ResourceKindListQuery::builder()
            ->filterByResourceClass('a')
            ->filterByResourceClass('a')
            ->build();
        $this->assertEquals(['a'], $query->getResourceClasses());
    }

    public function testSettingMetadataId() {
        $metadataId = 1;
        $query = ResourceKindListQuery::builder()->filterByMetadataId($metadataId)->build();
        $this->assertEquals($metadataId, $query->getMetadataId());
    }
}
