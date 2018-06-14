<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindDeleteCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindDeleteCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindDeleteCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindRepository */
    private $resourceKindRespository;
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRespository;
    /** @var ResourceKindDeleteCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resourceKindRespository = $this->createMock(ResourceKindRepository::class);
        $this->metadataRespository = $this->createMock(MetadataRepository::class);
        $this->handler = new ResourceKindDeleteCommandHandler($this->resourceKindRespository, $this->metadataRespository);
    }

    public function testDeletingResourceKind() {
        $rk = $this->createMock(ResourceKind::class);
        $command = new ResourceKindDeleteCommand($rk);
        $this->resourceKindRespository->expects($this->once())->method('delete')->with($rk);
        $this->handler->handle($command);
    }
}
