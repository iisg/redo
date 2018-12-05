<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\Traits\StubsTrait;

class ResourceContentsTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testForeach() {
        $contents = ResourceContents::fromArray([1 => 'a', 2 => 'b']);
        $valuesFromForeach = [];
        foreach ($contents as $metadataId => $values) {
            $valuesFromForeach[$metadataId] = $values[0]['value'];
        }
        $this->assertEquals([1 => 'a', 2 => 'b'], $valuesFromForeach);
    }

    public function testFiltersOutEmptyMetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => []]);
        $filtered = $contents->filterOutEmptyMetadata();
        $this->assertEquals(ResourceContents::fromArray([1 => 1]), $filtered);
    }

    public function testFiltersOutEmptySubmetadata() {
        $contents = ResourceContents::fromArray([1 => [['value' => 1, 'submetadata' => [13 => 1, 14 => []]]]]);
        $filtered = $contents->filterOutEmptyMetadata();
        $this->assertEquals(ResourceContents::fromArray([1 => [['value' => 1, 'submetadata' => [13 => 1]]]]), $filtered);
    }

    public function testDoesNotLeaveEmptySubmetadataArray() {
        $contents = ResourceContents::fromArray([1 => [['value' => 1, 'submetadata' => [14 => []]]]]);
        $filtered = $contents->filterOutEmptyMetadata();
        $this->assertEquals(ResourceContents::fromArray([1 => 1]), $filtered);
    }

    public function testDoesNotChangeOriginalContentsWhenFilteringEmptyMetadata() {
        $contents = ResourceContents::fromArray([1 => []]);
        $filtered = $contents->filterOutEmptyMetadata();
        $this->assertEquals(ResourceContents::fromArray([]), $filtered);
        $this->assertEquals(ResourceContents::fromArray([1 => []]), $contents);
    }

    public function testMapAllValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => 2]);
        $mapped = $contents->mapAllValues(
            function (MetadataValue $value) {
                return $value->withNewValue($value->getValue() * 2);
            }
        );
        $this->assertEquals(ResourceContents::fromArray([1 => 2, 2 => 4]), $mapped);
    }

    public function testMapAllValuesDoesNotChangeOriginalContents() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => 2]);
        $contents->mapAllValues(
            function (MetadataValue $value) {
                return $value->withNewValue($value->getValue() * 2);
            }
        );
        $this->assertEquals(ResourceContents::fromArray([1 => 1, 2 => 2]), $contents);
    }

    public function testMappingValuesToNullRemovesThem() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => 2]);
        $contents = $contents->mapAllValues(
            function (MetadataValue $value, $metadataId) {
                return $metadataId == 1 ? null : $value;
            }
        );
        $this->assertEquals(ResourceContents::fromArray([1 => [], 2 => 2]), $contents);
    }

    public function testMapAllValuesWithSubmetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [3 => 4]]]]);
        $mapped = $contents->mapAllValues(
            function (MetadataValue $value) {
                return $value->withNewValue($value->getValue() * 2);
            }
        );
        $this->assertEquals(ResourceContents::fromArray([1 => 2, 2 => [['value' => 4, 'submetadata' => [3 => 8]]]]), $mapped);
    }

    public function testReduceAllValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => 2]);
        $sum = $contents->reduceAllValues(
            function (MetadataValue $value, $metadataId, $acc) {
                return $value->getValue() + $metadataId + $acc;
            },
            0
        );
        $this->assertEquals(6, $sum);
    }

    public function testReduceAllValuesWithSubmetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [4 => 5]]]]);
        $sum = $contents->reduceAllValues(
            function (MetadataValue $value, $metadataId, $acc) {
                return $value->getValue() + $metadataId + $acc;
            },
            0
        );
        $this->assertEquals(15, $sum);
    }

    public function testForEachValue() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [4 => 5]]]]);
        $count = 0;
        $contents->forEachValue(
            function () use (&$count) {
                $count++;
            }
        );
        $this->assertEquals(3, $count);
    }

    public function testForEachMetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [4 => 5, 2 => [1, 2]]]]]);
        $iteratedIds = [];
        $iteratedValues = [];
        $contents->forEachMetadata(
            function (array $values, int $metadataId) use (&$iteratedIds, &$iteratedValues) {
                $iteratedIds[] = $metadataId;
                $iteratedValues[] = $values;
            }
        );
        $this->assertEquals([1, 2, 4, 2], $iteratedIds);
        $this->assertEquals([[1], [2], [5], [1, 2]], $iteratedValues);
    }

    public function testSetOneValue() {
        $contents = ResourceContents::fromArray([1 => 1]);
        $contents = $contents->withReplacedValues(2, 2);
        $this->assertEquals(ResourceContents::fromArray([1 => 1, 2 => 2]), $contents);
    }

    public function testReplaceOneValue() {
        $contents = ResourceContents::fromArray([1 => 1]);
        $contents = $contents->withReplacedValues(1, 2);
        $this->assertEquals(ResourceContents::fromArray([1 => 2]), $contents);
    }

    public function testAddOneValue() {
        $contents = ResourceContents::fromArray([1 => 1]);
        $contents = $contents->withMergedValues(1, 2);
        $this->assertEquals(ResourceContents::fromArray([1 => [1, 2]]), $contents);
    }

    public function testAddOneValueIfNotExistedBefore() {
        $contents = ResourceContents::fromArray([1 => 1]);
        $contents = $contents->withMergedValues(2, 2);
        $this->assertEquals(ResourceContents::fromArray([1 => 1, 2 => 2]), $contents);
    }

    public function testGetValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2], ['value' => 4]]]);
        $this->assertEquals([new MetadataValue(2), new MetadataValue(4)], $contents->getValues(2));
    }

    public function testGetValuesWithoutSubmetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2], ['value' => 4]]]);
        $this->assertEquals([2, 4], $contents->getValuesWithoutSubmetadata(2));
    }

    public function testGetValuesOfNotExistingMetadata() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2], ['value' => 4]]]);
        $this->assertEmpty($contents->getValues(666));
    }

    public function testGetValuesIncludesSubmetadataValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [2 => 5]]]]);
        $this->assertCount(2, $contents->getValues(2));
        $this->assertEquals(2, $contents->getValues(2)[0]->getValue());
        $this->assertEquals(5, $contents->getValues(2)[1]->getValue());
        $this->assertCount(1, $contents->getValues(2)[0]->getSubmetadata());
        $this->assertCount(0, $contents->getValues(2)[1]->getSubmetadata());
    }

    public function testGetValuesWithoutSubmetadataIncludesSubmetadataValues() {
        $contents = ResourceContents::fromArray([1 => 1, 2 => [['value' => 2, 'submetadata' => [2 => 5]]]]);
        $this->assertEquals([2, 5], $contents->getValuesWithoutSubmetadata(2));
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

    public function testWithMetadataNamesMappedToIds() {
        $metadataRepository = $this->createMock(MetadataRepository::class);
        $metadataRepository->method('findByName')
            ->willReturnOnConsecutiveCalls($this->createMetadataMock(1), $this->createMetadataMock(2));
        $contents = ResourceContents::fromArray(['Tytuł' => 'AA', 'Opis' => 'BB']);
        $contents = $contents->withMetadataNamesMappedToIds($metadataRepository);
        $this->assertEquals(ResourceContents::fromArray([1 => 'AA', 2 => 'BB']), $contents);
    }

    public function testWithMetadataNamesMappedToIdsForSubmetadata() {
        $metadataRepository = $this->createMock(MetadataRepository::class);
        $metadataRepository->method('findByName')
            ->willReturnOnConsecutiveCalls($this->createMetadataMock(2), $this->createMetadataMock(1));
        $contents = ResourceContents::fromArray(['Tytuł' => [['value' => 'AA', 'submetadata' => ['Opis' => 'BB']]]]);
        $contents = $contents->withMetadataNamesMappedToIds($metadataRepository);
        $this->assertEquals(ResourceContents::fromArray([1 => [['value' => 'AA', 'submetadata' => [2 => 'BB']]]]), $contents);
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
            [[1 => [new MetadataValue(2)]], [1 => [['value' => 2]]]],
            [
                [1 => [['value' => 'a', 'submetadata' => [1 => [new MetadataValue('a')]]]]],
                [1 => [['value' => 'a', 'submetadata' => [1 => [['value' => 'a']]]]]],
            ],
        ];
    }

    public function testCreatingWithValueProducer() {
        $rc = ResourceContents::fromArray(
            [1 => 1, 2 => 2],
            function ($value) {
                return $value * 2;
            }
        );
        $this->assertEquals([2], $rc->getValuesWithoutSubmetadata(1));
        $this->assertEquals([4], $rc->getValuesWithoutSubmetadata(2));
    }
}
