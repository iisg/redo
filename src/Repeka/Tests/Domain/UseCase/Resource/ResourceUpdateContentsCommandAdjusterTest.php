<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\ResourceContentsNormalizer;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandAdjuster;
use Repeka\Tests\Traits\StubsTrait;

class ResourceUpdateContentsCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    protected function setUp() {
        $resourceKind = $this->createMock(ResourceKind::class);
        $this->resource = $this->createResourceMock(1, $resourceKind);
    }

    public function testPreparesTheContent() {
        $contentsNormalizer = $this->createMock(ResourceContentsNormalizer::class);
        $adjuster = new ResourceUpdateContentsCommandAdjuster($contentsNormalizer);
        $contentsNormalizer->expects($this->once())->method('normalize')->willReturnArgument(0);
        $command = new ResourceUpdateContentsCommand($this->resource, []);
        $adjuster->adjustCommand($command);
    }
}
