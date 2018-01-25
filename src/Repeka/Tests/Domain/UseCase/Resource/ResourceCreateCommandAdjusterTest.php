<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\ResourceContentsNormalizer;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandAdjuster;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKind */
    private $resourceKind;

    protected function setUp() {
        $this->resourceKind = $this->createResourceKindMock();
    }

    public function testPreparesTheContent() {
        $contentsNormalizer = $this->createMock(ResourceContentsNormalizer::class);
        $validator = new ResourceCreateCommandAdjuster($contentsNormalizer);
        $contentsNormalizer->expects($this->once())->method('normalize')->willReturnArgument(0);
        $command = new ResourceCreateCommand($this->resourceKind, []);
        $validator->adjustCommand($command);
    }
}
