<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\LongAnalyzedStringIndexedMetadata;

class LongAnalyzedStringIndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    const TEST_STRING = 'lorem ipsum';
    const TEST_LANGUAGE = 'testLang';
    const TEST_PAGE_NUMBER = 5;

    public function testSettingValidPageNumber() {
        $metadata = new LongAnalyzedStringIndexedMetadata('whatever', self::TEST_LANGUAGE, self::TEST_STRING, self::TEST_PAGE_NUMBER);
        $this->assertEquals(self::TEST_PAGE_NUMBER, $metadata->getPageNumber());
    }

    public function testToArray() {
        $metadata = new LongAnalyzedStringIndexedMetadata('whatever', self::TEST_LANGUAGE, self::TEST_STRING, self::TEST_PAGE_NUMBER);
        $array = $metadata->toArray();
        $this->assertEquals(self::TEST_STRING, $array[ResourceConstants::longLanguageString(self::TEST_LANGUAGE)]);
        $this->assertEquals(self::TEST_PAGE_NUMBER, $array[ResourceConstants::INTEGER]);
        $this->assertCount(3, $array);
    }

    public function testRequiredMapping() {
        $languages = [
            'a' => null,
            'b' => 'B',
        ];
        $requiredMapping = LongAnalyzedStringIndexedMetadata::getRequiredMapping($languages);
        $this->assertEquals('string', $requiredMapping[ResourceConstants::longLanguageString('a')]['type']);
        $this->assertEquals('string', $requiredMapping[ResourceConstants::longLanguageString('b')]['type']);
        $this->assertEquals('B', $requiredMapping[ResourceConstants::longLanguageString('b')]['analyzer']);
    }
}
