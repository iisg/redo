<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCloneCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceCloneCommandHandler */
    private $handler;
    /** @var  CommandBus */
    private $commandBus;
    /** @var  ResourceCloneCommand */
    private $command;
    private $resourceKind;
    private $resource;
    private $user;

    protected function setUp() {
        $this->user = new UserEntity();
        $this->resourceKind = $this->createResourceKindMock();
        $this->resource = $this->createResourceMock(1);
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->fileHelper = $this->createMock(ResourceFileHelper::class);
    }

    public function testCloningResource() {
        $this->handler = new ResourceCloneCommandHandler($this->commandBus);
        $this->command = new ResourceCloneCommand(
            $this->resourceKind,
            $this->resource,
            ResourceContents::fromArray(['1' => ['AA']]),
            $this->user
        );
        $this->commandBus->method('handle')->willReturn($this->createMock(ResourceEntity::class));
        $this->handler->handle($this->command);
    }
}
