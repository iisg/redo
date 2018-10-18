<?php
namespace Repeka\Domain\MetadataImport\Transform;

use Repeka\Domain\Service\ResourceDisplayStrategyDependencyMap;
use Repeka\Domain\Service\ResourceDisplayStrategyUsedMetadataCollector;
use Repeka\Tests\Traits\StubsTrait;

class ResourceDisplayStrategyDependencyMapTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testMap() {
        $collector = new ResourceDisplayStrategyUsedMetadataCollector();
        $collector->addUsedMetadata(2, $this->createResourceMock(1));
        $map = new ResourceDisplayStrategyDependencyMap(30, $collector);
        $this->assertEquals(['1/2' => [30]], $map->toArray());
    }

    public function testMultimap() {
        $collector = (new ResourceDisplayStrategyUsedMetadataCollector())
            ->addUsedMetadata(2, $this->createResourceMock(1))
            ->addUsedMetadata(2, $this->createResourceMock(2))
            ->addUsedMetadata(3, $this->createResourceMock(1))
            ->addUsedMetadata(4, $this->createResourceMock(4));
        $map = new ResourceDisplayStrategyDependencyMap(30, $collector);
        $this->assertEquals(['1/2' => [30], '2/2' => [30], '1/3' => [30], '4/4' => [30]], $map->toArray());
    }

    public function testMerging() {
        $collector1 = (new ResourceDisplayStrategyUsedMetadataCollector())
            ->addUsedMetadata(2, $this->createResourceMock(1))
            ->addUsedMetadata(2, $this->createResourceMock(2));
        $map = new ResourceDisplayStrategyDependencyMap(30, $collector1);
        $collector2 = (new ResourceDisplayStrategyUsedMetadataCollector())
            ->addUsedMetadata(2, $this->createResourceMock(2))
            ->addUsedMetadata(2, $this->createResourceMock(3));
        $map = $map->merge(new ResourceDisplayStrategyDependencyMap(40, $collector2));
        $this->assertEquals(['1/2' => [30], '2/2' => [30, 40], '3/2' => [40]], $map->toArray());
    }

    public function testClearing() {
        $collector1 = (new ResourceDisplayStrategyUsedMetadataCollector())
            ->addUsedMetadata(2, $this->createResourceMock(1))
            ->addUsedMetadata(2, $this->createResourceMock(2));
        $map = new ResourceDisplayStrategyDependencyMap(30, $collector1);
        $collector2 = (new ResourceDisplayStrategyUsedMetadataCollector())
            ->addUsedMetadata(2, $this->createResourceMock(2))
            ->addUsedMetadata(2, $this->createResourceMock(3));
        $map = $map->merge(new ResourceDisplayStrategyDependencyMap(40, $collector2));
        $map = $map->clear(30);
        $this->assertEquals(['2/2' => [40], '3/2' => [40]], $map->toArray());
    }

    public function testGetDependentMetadataIds() {
        $collector1 = (new ResourceDisplayStrategyUsedMetadataCollector())
            ->addUsedMetadata(2, $this->createResourceMock(1))
            ->addUsedMetadata(2, $this->createResourceMock(2));
        $map = new ResourceDisplayStrategyDependencyMap(30, $collector1);
        $collector2 = (new ResourceDisplayStrategyUsedMetadataCollector())
            ->addUsedMetadata(2, $this->createResourceMock(2))
            ->addUsedMetadata(1, $this->createResourceMock(2));
        $map = $map->merge(new ResourceDisplayStrategyDependencyMap(40, $collector2));
        $this->assertEquals([30], $map->getDependentMetadataIds($this->createResourceMock(1), [2]));
        $this->assertEquals([30, 40], $map->getDependentMetadataIds($this->createResourceMock(2), [2]));
        $this->assertEquals([40], $map->getDependentMetadataIds($this->createResourceMock(2), [1]));
        $this->assertEquals([30], $map->getDependentMetadataIds($this->createResourceMock(1), [1, 2, 3]));
        $this->assertEquals([30, 40], $map->getDependentMetadataIds($this->createResourceMock(2), [2, 1]));
    }
}
