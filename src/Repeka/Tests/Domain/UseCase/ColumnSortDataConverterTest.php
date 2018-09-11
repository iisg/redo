<?php
namespace Repeka\Tests\Domain\UseCase;

use Repeka\Domain\UseCase\ColumnSortDataConverter;

class ColumnSortDataConverterTest extends \PHPUnit_Framework_TestCase {

    /** @var  ColumnSortDataConverter */
    private $columnSortDataConverter;

    protected function setUp() {
        $this->columnSortDataConverter = new ColumnSortDataConverter();
    }

    public function testPrepareSortByArray() {
        $expectedData = [
            ['columnId' => 2, 'direction' => 'ASC', 'language' => 'PL'],
            ['columnId' => 'id', 'direction' => 'DESC', 'language' => 'PL']
        ];
        $preparedData = $this->columnSortDataConverter->convertSortByMetadataColumnsToIntegers(
            [['columnId' => '2', 'direction' => 'ASC', 'language' => 'PL'], ['columnId' => 'id', 'direction' => 'DESC', 'language' => 'PL']]
        );
        $this->assertEquals($expectedData, $preparedData);
    }
}
