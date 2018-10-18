<?php
namespace Repeka\Domain\MetadataImport\Transform;

use Repeka\Domain\Service\ResourceDisplayStrategyUsedMetadataCollector;
use Repeka\Tests\Traits\StubsTrait;

class ResourceDisplayStrategyUsedMetadataCollectorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceDisplayStrategyUsedMetadataCollector */
    private $collector;

    /** @before */
    public function init() {
        $this->collector = new ResourceDisplayStrategyUsedMetadataCollector();
    }

    public function testCollecting() {
        $this->assertEmpty($this->collector->getUsedMetadata());
        $this->collector->addUsedMetadata(1, $this->createResourceMock(1));
        $this->assertEquals([1 => [1]], $this->collector->getUsedMetadata());
        $this->collector->addUsedMetadata(1, $this->createResourceMock(2));
        $this->assertEquals([1 => [1, 2]], $this->collector->getUsedMetadata());
        $this->collector->addUsedMetadata(2, $this->createResourceMock(2));
        $this->assertEquals([1 => [1, 2], 2 => [2]], $this->collector->getUsedMetadata());
    }
}
