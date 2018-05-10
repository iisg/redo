<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\IndexedMetadata;

class IndexedMetadataTest extends \PHPUnit_Framework_TestCase {
    public function testRequiredConstructorParameters() {
        $typeName = 'metadataTypeName';
        $metadata = new TestIndexedMetadata($typeName);
        $this->assertEquals($typeName, $metadata->getTypeName());
        $this->assertNull($metadata->getValue());
    }

    public function testSettingValueInConstructor() {
        $value = 'lorem ipsum';
        $metadata = new TestIndexedMetadata('whatever', $value);
        $this->assertEquals($value, $metadata->getValue());
    }

    public function testSettingValue() {
        $value = 1;
        $metadata = new TestIndexedMetadata('whatever');
        $metadata->setValue($value);
        $this->assertEquals($value, $metadata->getValue());
    }

    public function testValidation() {
        $this->expectException('InvalidArgumentException');
        new TestIndexedMetadata('whatever', 1, false);
    }

    public function testToArray() {
        $typeName = 'testTypeName';
        $result = (new TestIndexedMetadata($typeName, 'testValue'))->toArray();
        $this->assertEquals([ResourceConstants::VALUE_TYPE => $typeName], $result);
    }

    public function testToArrayWithChildren() {
        $typeName = 'testTypeName';
        $child = new TestIndexedMetadata($typeName, 'childValue');
        $metadata = new TestIndexedMetadata($typeName, 'testValue');
        $metadata->addMetadata($child);
        $array = $metadata->toArray();
        $this->assertEquals(
            [
                ResourceConstants::VALUE_TYPE => $typeName,
                ResourceConstants::CHILDREN => [$child->toArray()],
            ],
            $array
        );
    }
}

// @codingStandardsIgnoreStart
class TestIndexedMetadata extends IndexedMetadata {
// @codingStandardsIgnoreEnd
    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(string $typeName, $value = null, bool $isValid = true) {
        $validator = function () use ($isValid) {
            return $isValid;
        };
        parent::__construct($typeName, $validator, $value);
    }
}
