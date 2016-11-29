<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\DateTimeIndexedMetadata;

class DateTimeIndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    const TEST_TIMESTAMP = 1480550400;
    private $testDate;

    protected function setUp() {
        $this->testDate = new \DateTime('@' . self::TEST_TIMESTAMP, new \DateTimeZone('UTC'));
    }

    public function testSettingValidValue() {
        $metadata = new DateTimeIndexedMetadata('whatever', $this->testDate);
        $this->assertEquals($this->testDate, $metadata->getValue());
    }

    public function testSettingInvalidValue() {
        $this->expectException('InvalidArgumentException');
        new DateTimeIndexedMetadata('whatever', '5');
    }

    public function testToArray() {
        $metadata = new DateTimeIndexedMetadata('whatever', $this->testDate);
        $array = $metadata->toArray();
        $this->assertEquals(self::TEST_TIMESTAMP * 1000, $array[ResourceConstants::DATETIME]);
        $this->assertCount(2, $array);
    }
}
