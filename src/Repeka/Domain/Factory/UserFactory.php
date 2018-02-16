<?php
namespace Repeka\Domain\Factory;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;

abstract class UserFactory {
    use CommandBusAware;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function createUser(string $username, ?string $plainPassword, ResourceContents $userData): User {
        $resourceKind = $this->resourceKindRepository->findOne(SystemResourceKind::USER);
        $resourceCreateCommand = new ResourceCreateCommand($resourceKind, $userData);
        /** @var ResourceEntity $userData */
        $userData = $this->handleCommand($resourceCreateCommand);
        $user = $this->createApplicationUser($username, $plainPassword, $userData);
        return $user;
    }

    abstract protected function createApplicationUser(string $username, ?string $plainPassword, ResourceEntity $userData): User;
}
