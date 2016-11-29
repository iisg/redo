<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\RawStringIndexedMetadata;

class RawStringIndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    const TEST_STRING = 'lorem ipsum';

    public function testSettingValidValue() {
        $metadata = new RawStringIndexedMetadata('whatever', self::TEST_STRING);
        $this->assertEquals(self::TEST_STRING, $metadata->getValue());
    }

    public function testSettingInvalidValue() {
        $this->expectException('InvalidArgumentException');
        new RawStringIndexedMetadata('whatever', 5);
    }

    public function testToArray() {
        $metadata = new RawStringIndexedMetadata('whatever', self::TEST_STRING);
        $array = $metadata->toArray();
        $this->assertEquals(self::TEST_STRING, $array[ResourceConstants::RAW_STRING]);
        $this->assertCount(2, $array);
    }
}
