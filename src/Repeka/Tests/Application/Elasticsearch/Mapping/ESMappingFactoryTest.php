<?php
namespace Repeka\Tests\Application\Elasticsearch\Mapping;

use Repeka\Application\Elasticsearch\Mapping\ESMappingFactory;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\IndexedMetadata;

class ESMappingFactoryTest extends \PHPUnit_Framework_TestCase {
    public function testLanguageAgnosticMapping() {
        $factory = new ESMappingFactory(1, [], [], [
            'Repeka\Tests\Application\Elasticsearch\Mapping\MappingFactoryTestMetadata1',
            'Repeka\Tests\Application\Elasticsearch\Mapping\MappingFactoryTestMetadata2Value1'
        ]);
        $mapping = $factory->getMappingArray();
        $this->assertArrayHasKey(ResourceConstants::CHILDREN, $mapping);
        $this->assertEquals([
            'type' => 'nested',
            'properties' => [
                MappingFactoryTestMetadata1::FIELD1 => MappingFactoryTestMetadata1::VALUE1,
                MappingFactoryTestMetadata1::FIELD2 => MappingFactoryTestMetadata1::VALUE2,
                MappingFactoryTestMetadata2Value1::FIELD => MappingFactoryTestMetadata2Value1::VALUE
            ]
        ], $mapping[ResourceConstants::CHILDREN]);
    }

    public function testMappingDepth() {
        $nestingDepth = 3;
        $factory = new ESMappingFactory($nestingDepth, [], [], [
            'Repeka\Tests\Application\Elasticsearch\Mapping\MappingFactoryTestMetadata2Value1'
        ]);
        $mapping = $factory->getMappingArray();
        for ($depth = 0; $depth < $nestingDepth; $depth++) {
            $this->assertEquals('nested', $mapping[ResourceConstants::CHILDREN]['type']);
            $this->assertEquals(
                MappingFactoryTestMetadata2Value1::VALUE,
                $mapping[ResourceConstants::CHILDREN]['properties'][MappingFactoryTestMetadata2Value1::FIELD]
            );
            $this->assertCount(2, $mapping[ResourceConstants::CHILDREN]);
            $mapping = $mapping[ResourceConstants::CHILDREN]['properties'];
        }
        $this->assertArrayNotHasKey(ResourceConstants::CHILDREN, $mapping);
    }

    public function testLanguageStringFields() {
        $factory = new ESMappingFactory(1, ['en', 'de', 'test'], [], [
            'Repeka\Tests\Application\Elasticsearch\Mapping\MappingFactoryLocalizedTestMetadata'
        ]);
        $mapping = $factory->getMappingArray();
        $expectedFields = [
            MappingFactoryLocalizedTestMetadata::field('en'),
            MappingFactoryLocalizedTestMetadata::field('de'),
            MappingFactoryLocalizedTestMetadata::field('test'),
        ];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $mapping[ResourceConstants::CHILDREN]['properties']);
        }
        $this->assertCount(count($expectedFields), $mapping[ResourceConstants::CHILDREN]['properties']);
    }

    public function testConflictingMappingRequirements() {
        $this->expectException('Exception');
        $factory = new ESMappingFactory(1, [], [], [
            'Repeka\Tests\Application\Elasticsearch\Mapping\MappingFactoryTestMetadata2Value1',
            'Repeka\Tests\Application\Elasticsearch\Mapping\MappingFactoryTestMetadata2Value2'
        ]);
        $factory->getMappingArray();
    }

    public function testAssigningAnalyzers() {
        $factory = new ESMappingFactory(1, ['en', 'test'], ['test' => 'testAnalyzer'], [
            'Repeka\Tests\Application\Elasticsearch\Mapping\MappingFactoryLocalizedTestMetadata'
            ]);
        $mapping = $factory->getMappingArray();
        $properties = $mapping[ResourceConstants::CHILDREN]['properties'];
        $this->assertEquals('testAnalyzer', $properties[MappingFactoryLocalizedTestMetadata::field('test')]);
        $this->assertNull($properties[MappingFactoryLocalizedTestMetadata::field('en')]);
    }
}

// @codingStandardsIgnoreStart
class MappingFactoryTestMetadata1 extends IndexedMetadata {
    const FIELD1 = '1field1';
    const FIELD2 = '1field2';
    const VALUE1 = '1value1';
    const VALUE2 = '1value2';

    public function __construct() {
        parent::__construct('whatever1', function () {
            return true;
        }, 'whatever');
    }

    public static function getRequiredMapping(array $languages): array {
        return [
            self::FIELD1 => self::VALUE1,
            self::FIELD2 => self::VALUE2
        ];
    }
}

class MappingFactoryTestMetadata2Value1 extends IndexedMetadata {
    const FIELD = '2field';
    const VALUE = '2value1';

    public function __construct() {
        parent::__construct('whatever1', function () {
            return true;
        }, 'whatever');
    }

    public static function getRequiredMapping(array $languages): array {
        return [self::FIELD => self::VALUE];
    }
}

class MappingFactoryTestMetadata2Value2 extends IndexedMetadata {
    const FIELD = '2field';
    const VALUE = '2value2';

    public function __construct() {
        parent::__construct('whatever1', function () {
            return true;
        }, 'whatever');
    }

    public static function getRequiredMapping(array $languages): array {
        return [self::FIELD => self::VALUE];
    }
}

class MappingFactoryLocalizedTestMetadata extends IndexedMetadata {
    const FIELD = 'field_%s';

    public function __construct() {
        parent::__construct('whatever1', function () {
            return true;
        }, 'whatever');
    }

    public static function getRequiredMapping(array $languages): array {
        $result = [];
        foreach ($languages as $language => $analyzer) {
            $result[self::field($language)] = $analyzer;
        }
        return $result;
    }

    public static function field($language) {
        return sprintf(self::FIELD, $language);
    }
}
