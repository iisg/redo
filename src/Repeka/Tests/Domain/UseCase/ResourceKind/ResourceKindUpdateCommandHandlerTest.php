<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindUpdateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindRepository */
    private $resourceKindRespository;
    /** @var ResourceKindUpdateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resourceKindRespository = $this->createMock(ResourceKindRepository::class);
        $this->handler = new ResourceKindUpdateCommandHandler($this->resourceKindRespository);
    }

    public function testUpdatingResourceKind() {
        $rk = $this->createMock(ResourceKind::class);
        $metadataList = [$this->createMetadataMock()];
        $rk->expects($this->once())->method('setMetadataList')->with($metadataList);
        $command = new ResourceKindUpdateCommand($rk, [], $metadataList, []);
        $this->resourceKindRespository->expects($this->once())->method('save')->with($rk)->willReturnArgument(0);
        $updated = $this->handler->handle($command);
        $this->assertEquals($rk, $updated);
    }
}
