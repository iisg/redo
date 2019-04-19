<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;

abstract class UserFactory {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(ResourceKindRepository $resourceKindRepository, CommandBus $commandBus) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->commandBus = $commandBus;
    }

    public function createUser(string $username, ?string $plainPassword, ResourceContents $userData): User {
        $resourceKind = $this->resourceKindRepository->findOne(SystemResourceKind::USER);
        $resourceCreateCommand = new ResourceCreateCommand($resourceKind, $userData);
        /** @var ResourceEntity $userData */
        $userData = $this->commandBus->handle($resourceCreateCommand);
        $user = $this->createApplicationUser($username, $plainPassword, $userData);
        return $user;
    }

    abstract public function updatePassword(User $user, string $newPlainPassword): User;

    abstract protected function createApplicationUser(string $username, ?string $plainPassword, ResourceEntity $userData): User;
}
