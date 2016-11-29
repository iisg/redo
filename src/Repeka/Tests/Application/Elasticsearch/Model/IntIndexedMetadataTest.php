<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\IntIndexedMetadata;

class IntIndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    const TEST_INT = 5;

    public function testSettingValidValue() {
        $metadata = new IntIndexedMetadata('whatever', self::TEST_INT);
        $this->assertEquals(self::TEST_INT, $metadata->getValue());
    }

    public function testSettingInvalidValue() {
        $this->expectException('InvalidArgumentException');
        new IntIndexedMetadata('whatever', '5');
    }

    public function testToArray() {
        $metadata = new IntIndexedMetadata('whatever', self::TEST_INT);
        $array = $metadata->toArray();
        $this->assertEquals(self::TEST_INT, $array[ResourceConstants::INTEGER]);
        $this->assertCount(2, $array);
    }
}
