<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindRepository */
    private $resourceKindRespository;
    /** @var ResourceKindCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resourceKindRespository = $this->createMock(ResourceKindRepository::class);
        $this->handler = new ResourceKindCreateCommandHandler($this->resourceKindRespository);
    }

    public function testCreatingResourceKind() {
        $metadataList = [$this->createMetadataMock()];
        $command = new ResourceKindCreateCommand('rk_name', ['PL' => 'Labelka'], $metadataList);
        $this->resourceKindRespository->expects($this->once())->method('save')->willReturnArgument(0);
        $created = $this->handler->handle($command);
        $this->assertEquals('rk_name', $created->getName());
        $this->assertEquals(['PL' => 'Labelka'], $created->getLabel());
        $this->assertEquals($metadataList, $created->getMetadataList());
    }
}
