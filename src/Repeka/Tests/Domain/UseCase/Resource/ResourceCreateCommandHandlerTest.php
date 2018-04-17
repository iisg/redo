<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceCreateCommandHandler */
    private $handler;
    /** @var  CommandBus */
    private $commandBus;
    /** @var  ResourceCreateCommand */
    private $command;

    protected function setUp() {
        $user = new UserEntity();
        $resourceKind = $this->createMock(ResourceKind::class);
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->handler = new ResourceCreateCommandHandler($this->commandBus);
        $this->command = new ResourceCreateCommand($resourceKind, ResourceContents::fromArray(['1' => ['AA']]), $user);
    }

    public function testUpdatingResource() {
        $this->commandBus->method('handle')->willReturn($this->createMock(ResourceEntity::class));
        $this->handler->handle($this->command);
    }
}
