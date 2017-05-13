<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandHandler;

abstract class UserFactory {

    /**
     * @var ResourceKindRepository
     */
    private $resourceKindRepository;
    /**
     * @var ResourceCreateCommandHandler
     */
    private $resourceCreateCommandHandler;

    public function __construct(
        ResourceKindRepository $resourceKindRepository,
        ResourceCreateCommandHandler $resourceCreateCommandHandler
    ) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceCreateCommandHandler = $resourceCreateCommandHandler;
    }

    public function createUser(string $username, ?string $plainPassword, ?string $email, array $userData = []): User {
        $resourceKind = $this->resourceKindRepository->findOne(SystemResourceKind::USER);
        $resourceCreateCommand = new ResourceCreateCommand($resourceKind, $userData);
        /** @var ResourceEntity $userData */
        $userData = $this->resourceCreateCommandHandler->handle($resourceCreateCommand);
        $user = $this->createApplicationUser($username, $plainPassword, $email, $userData);
        return $user;
    }

    abstract protected function createApplicationUser(
        string $username,
        string $plainPassword,
        string $email,
        ResourceEntity $userData
    ): User;
}
