<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\UseCase\ColumnSortDataConverter;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQueryAdjuster;

class ResourceKindListQueryAdjusterTest extends \PHPUnit_Framework_TestCase {

    public function testAdjustResourceKindListQuery() {
        $columnSortDataConverter = $this->createMock(ColumnSortDataConverter::class);
        $columnSortDataConverter->expects($this->once())->method('convertSortByMetadataColumnsToIntegers');
        $adjuster = new ResourceKindListQueryAdjuster($columnSortDataConverter);
        $command = ResourceKindListQuery::builder()->build();
        $adjuster->adjustCommand($command);
    }
}
