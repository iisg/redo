<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\TokenizedStringIndexedMetadata;

class TokenizedStringIndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    const TEST_STRING = 'lorem ipsum';

    public function testSettingValidValue() {
        $metadata = new TokenizedStringIndexedMetadata('whatever', self::TEST_STRING);
        $this->assertEquals(self::TEST_STRING, $metadata->getValue());
    }

    public function testSettingInvalidValue() {
        $this->expectException('InvalidArgumentException');
        new TokenizedStringIndexedMetadata('whatever', 5);
    }

    public function testToArray() {
        $metadata = new TokenizedStringIndexedMetadata('whatever', self::TEST_STRING);
        $array = $metadata->toArray();
        $this->assertEquals(self::TEST_STRING, $array[ResourceConstants::TOKENIZED_STRING]);
        $this->assertCount(2, $array);
    }
}
