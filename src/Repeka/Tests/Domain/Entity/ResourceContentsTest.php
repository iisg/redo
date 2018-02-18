<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\ResourceContents;

class ResourceContentsTest extends \PHPUnit_Framework_TestCase {

    public function testForeach() {
        $contents = ResourceContents::fromArray([1 => 'a', 2 => 'b']);
        $valuesFromForeach = [];
        foreach ($contents as $metadataId => $values) {
            $valuesFromForeach[$metadataId] = $values[0]['value'];
        }
        $this->assertEquals([1 => 'a', 2 => 'b'], $valuesFromForeach);
    }

    public function testMapAllValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => 2]);
        $mapped = $contents->mapAllValues(function ($value) {
            return $value * 2;
        });
        $this->assertEquals(ResourceContents::fromArray([1 => 2, 2 => 4]), $mapped);
    }

    public function testMapAllValuesDoesNotChangeOriginalContents() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => 2]);
        $contents->mapAllValues(function ($value) {
            return $value * 2;
        });
        $this->assertEquals(ResourceContents::fromArray([1 => 1, 2 => 2]), $contents);
    }

    public function testReduceAllValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => 2]);
        $sum = $contents->reduceAllValues(function ($value, $metadataId, $acc) {
            return $value + $metadataId + $acc;
        }, 0);
        $this->assertEquals(6, $sum);
    }

    public function testReduceAllValuesWithSubmetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [4 => 5]]]]);
        $sum = $contents->reduceAllValues(function ($value, $metadataId, $acc) {
            return $value + $metadataId + $acc;
        }, 0);
        $this->assertEquals(15, $sum);
    }

    public function testForEachValue() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [4 => 5]]]]);
        $count = 0;
        $contents->forEachValue(function () use (&$count) {
            $count++;
        });
        $this->assertEquals(3, $count);
    }

    public function testForEachMetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [4 => 5, 2 => [1, 2]]]]]);
        $iteratedIds = [];
        $iteratedValues = [];
        $contents->forEachMetadata(function (array $values, int $metadataId) use (&$iteratedIds, &$iteratedValues) {
            $iteratedIds[] = $metadataId;
            $iteratedValues[] = $values;
        });
        $this->assertEquals([1, 2, 4, 2], $iteratedIds);
        $this->assertEquals([[1], [2], [5], [1, 2]], $iteratedValues);
    }

    public function testSetOneValue() {
        $contents = ResourceContents::fromArray([1 => 1]);
        $contents = $contents->withNewValues(2, 2);
        $this->assertEquals(ResourceContents::fromArray([1 => 1, 2 => 2]), $contents);
    }

    public function testGetValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2], ['value' => 4]]]);
        $this->assertEquals([2, 4], $contents->getValues(2));
    }

    public function testGetValuesOfNotExistingMetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2], ['value' => 4]]]);
        $this->assertEmpty($contents->getValues(666));
    }

    public function testGetValuesIncludesSubmetadataValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [2 => 5]]]]);
        $this->assertEquals([2, 5], $contents->getValues(2));
    }

    public function testChangingContentsIsForbidden() {
        $this->expectException(\LogicException::class);
        ResourceContents::fromArray([])[1] = 2;
    }

    public function testUnsettingMetadataValueIsForbidden() {
        $this->expectException(\LogicException::class);
        $contents = ResourceContents::fromArray([1 => 2]);
        unset($contents[1]);
    }

    public function testJsonEncode() {
        $contents = ResourceContents::fromArray([1 => 2]);
        $this->assertEquals('{"1":[{"value":2}]}', json_encode($contents));
    }

    /** @dataProvider fromArrayExamples */
    public function testFromArray(array $input, array $expectedOutput) {
        $normalized = ResourceContents::fromArray($input)->toArray();
        $this->assertEquals(
            $expectedOutput,
            $normalized,
            var_export($input, true) . "\nvvvvvvvv\n" . var_export($normalized, true)
        );
    }

    public function fromArrayExamples(): array {
        return [
            [[], []],
            [[1 => [['value' => 'abc']]], [1 => [['value' => 'abc']]]],
            [[1 => 'a'], [1 => [['value' => 'a']]]],
            [[1 => ['a']], [1 => [['value' => 'a']]]],
            [['a'], [[['value' => 'a']]]],
            [[2 => ['a', 'b']], [2 => [['value' => 'a'], ['value' => 'b']]]],
            [[1 => ['a']], [1 => [['value' => 'a']]]],
            [[1 => [['value' => 'a', 'someKey' => 'b']]], [1 => [['value' => 'a']]]],
            [[1 => [['value' => 'a', 'submetadata' => [1 => 'a']]]], [1 => [['value' => 'a', 'submetadata' => [1 => [['value' => 'a']]]]]]],
            [[1 => [['submetadata' => [1 => 'a']]]], [1 => [['value' => null, 'submetadata' => [1 => [['value' => 'a']]]]]]],
        ];
    }
}
