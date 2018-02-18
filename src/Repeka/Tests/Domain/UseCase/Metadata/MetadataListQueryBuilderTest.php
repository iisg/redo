<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;

class MetadataListQueryBuilderTest extends \PHPUnit_Framework_TestCase {
    public function testFilterByParent() {
        $parent = $this->createMock(Metadata::class);
        $query = MetadataListQuery::builder()->filterByParent($parent)->build();
        $this->assertSame($parent, $query->getParent());
    }

    public function testFilterByResourceClass() {
        $resourceClass = 'unicorns';
        $query = MetadataListQuery::builder()->filterByResourceClass($resourceClass)->build();
        $this->assertSame([$resourceClass], $query->getResourceClasses());
    }

    public function testFilterByControl() {
        $control = MetadataControl::RELATIONSHIP();
        $query = MetadataListQuery::builder()->filterByControl($control)->build();
        $this->assertSame([$control], $query->getControls());
    }

    public function testOnlyTopLevel() {
        $this->assertTrue(MetadataListQuery::builder()->onlyTopLevel()->build()->onlyTopLevel());
        $this->assertFalse(MetadataListQuery::builder()->build()->onlyTopLevel());
    }

    public function testDefaultValues() {
        $emptyQuery = MetadataListQuery::builder()->build();
        $this->assertEmpty($emptyQuery->getResourceClasses());
        $this->assertNull($emptyQuery->getParent());
        $this->assertEmpty($emptyQuery->getControls());
    }

    public function testIllegalParentAndTopLevelQuery() {
        $this->expectException(\InvalidArgumentException::class);
        MetadataListQuery::builder()->filterByParent($this->createMock(Metadata::class))->onlyTopLevel()->build();
    }
}
