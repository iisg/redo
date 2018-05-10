<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\AnalyzedStringIndexedMetadata;

class AnalyzedStringIndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    const TEST_STRING = 'lorem ipsum';
    const TEST_LANGUAGE = 'testLang';

    public function testCreatingWithInvalidLanguage() {
        $this->expectException('InvalidArgumentException');
        new AnalyzedStringIndexedMetadata('whatever', ' ', self::TEST_STRING);
    }

    public function testSettingValidValue() {
        $metadata = new AnalyzedStringIndexedMetadata('whatever', self::TEST_LANGUAGE, self::TEST_STRING);
        $this->assertEquals(self::TEST_STRING, $metadata->getValue());
    }

    public function testSettingInvalidValue() {
        $this->expectException('InvalidArgumentException');
        new AnalyzedStringIndexedMetadata('whatever', self::TEST_LANGUAGE, 3.14);
    }

    public function testToArray() {
        $metadata = new AnalyzedStringIndexedMetadata('whatever', self::TEST_LANGUAGE, self::TEST_STRING);
        $array = $metadata->toArray();
        $this->assertEquals(self::TEST_STRING, $array[ResourceConstants::languageString(self::TEST_LANGUAGE)]);
        $this->assertCount(2, $array);
    }

    public function testRequiredMapping() {
        $languages = [
            'a' => null,
            'b' => 'B',
            'c' => null,
        ];
        $requiredMapping = AnalyzedStringIndexedMetadata::getRequiredMapping($languages);
        $this->assertEquals('string', $requiredMapping[ResourceConstants::languageString('a')]['type']);
        $this->assertEquals('string', $requiredMapping[ResourceConstants::languageString('b')]['type']);
        $this->assertEquals('string', $requiredMapping[ResourceConstants::languageString('c')]['type']);
        $this->assertEquals('B', $requiredMapping[ResourceConstants::languageString('b')]['analyzer']);
    }
}
