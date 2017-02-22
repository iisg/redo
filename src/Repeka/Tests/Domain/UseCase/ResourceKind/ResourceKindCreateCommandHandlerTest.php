<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\ResourceKindFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandHandler;

class ResourceKindCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceKindCreateCommand */
    private $command;
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindRepository */
    private $resourceKindRespository;
    /** @var ResourceKindCreateCommandHandler */
    private $handler;

    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindFactory */
    private $resourceKindFactory;

    protected function setUp() {
        $this->command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->resourceKindRespository = $this->createMock(ResourceKindRepository::class);
        $this->resourceKindFactory = $this->createMock(ResourceKindFactory::class);
        $this->handler = new ResourceKindCreateCommandHandler($this->resourceKindFactory, $this->resourceKindRespository);
    }

    public function testCreatingResourceKind() {
        $resourceKind = new ResourceKind([]);
        $this->resourceKindFactory->expects($this->once())->method('create')->willReturn($resourceKind);
        $this->resourceKindRespository->expects($this->once())->method('save')->willReturnArgument(0);
        $created = $this->handler->handle($this->command);
        $this->assertSame($resourceKind, $created);
    }
}
