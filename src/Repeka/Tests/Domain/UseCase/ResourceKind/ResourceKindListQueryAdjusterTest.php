<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\UseCase\ColumnSortDataConverter;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQueryAdjuster;

class ResourceKindListQueryAdjusterTest extends \PHPUnit_Framework_TestCase {

    /** @var ResourceKindListQueryAdjuster */
    private $adjuster;

    public function setUp() {
        $columnSortDataConverter = $this->createMock(ColumnSortDataConverter::class);
        $columnSortDataConverter->expects($this->once())->method('convertSortByMetadataColumnsToIntegers');
        $this->adjuster = new ResourceKindListQueryAdjuster($columnSortDataConverter);
    }

    public function testAdjustResourceKindListQuery() {
        $command = ResourceKindListQuery::builder()->build();
        $this->adjuster->adjustCommand($command);
    }
}
