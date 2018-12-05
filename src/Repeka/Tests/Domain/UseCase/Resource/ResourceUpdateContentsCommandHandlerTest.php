<?php
namespace Repeka\Tests\UseCase\Resource;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceUpdateContentsCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceUpdateContentsCommand */
    private $command;
    /** @var ResourceUpdateContentsCommandHandler */
    private $handler;
    /** @var CommandBus */
    private $commandBus;

    protected function setUp() {
        $user = new UserEntity();
        $this->commandBus = $this->createMock(CommandBus::class);
        $resourceKind = $this->createResourceKindMock();
        $resource = $this->createResourceMock(1, $resourceKind);
        $this->command = new ResourceUpdateContentsCommand($resource, ResourceContents::fromArray(['1' => ['AA']]), $user);
        $this->handler = new ResourceUpdateContentsCommandHandler($this->commandBus);
    }

    public function testUpdatingResource() {
        $this->commandBus->method('handle')->willReturn($this->createMock(ResourceEntity::class));
        $this->handler->handle($this->command);
    }
}
