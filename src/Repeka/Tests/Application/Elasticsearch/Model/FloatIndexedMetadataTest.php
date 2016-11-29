<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\FloatIndexedMetadata;

class FloatIndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    const TEST_FLOAT = 3.14;

    public function testSettingValidValue() {
        $metadata = new FloatIndexedMetadata('whatever', self::TEST_FLOAT);
        $this->assertEquals(self::TEST_FLOAT, $metadata->getValue());
    }

    public function testSettingInvalidValue() {
        $this->expectException('InvalidArgumentException');
        new FloatIndexedMetadata('whatever', '3.14');
    }

    public function testToArray() {
        $metadata = new FloatIndexedMetadata('whatever', self::TEST_FLOAT);
        $array = $metadata->toArray();
        $this->assertEquals(self::TEST_FLOAT, $array[ResourceConstants::FLOAT]);
        $this->assertCount(2, $array);
    }
}
