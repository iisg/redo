<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class ExecuteTransitionBulkChange extends BulkChange {

    /** @var string $transitionId */
    private $transitionId;
    /** @var User $executor */
    private $executor;
    /** @var CommandBus $commandBus */
    private $commandBus;
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository, CommandBus $commandBus, array $changeConfig = []) {
        $this->commandBus = $commandBus;
        $this->userRepository = $userRepository;
        if ($changeConfig) {
            $this->transitionId = $changeConfig['transitionId'];
            $this->executor = $this->userRepository->findOne($changeConfig['executorId']);
        }
    }

    public function createForChange(array $changeConfig): BulkChange {
        return new self($this->userRepository, $this->commandBus, $changeConfig);
    }

    protected function getChangeConfig(): array {
        return ['executorId' => $this->executor->getId(), 'transitionId' => $this->transitionId];
    }

    public function apply(ResourceEntity $resource): ResourceEntity {
        $command = new ResourceTransitionCommand($resource, $resource->getContents(), $this->transitionId, $this->executor);
        return $this->commandBus->handle($command);
    }

    public function applyForPreview(ResourceEntity $resource): ResourceEntity {
        return $resource->applyTransition($this->transitionId);
    }
}
