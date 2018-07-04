<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\User\UserGrantRolesCommand;

class UpdateUserRolesAfterEditListener extends CommandEventsListener {
    /** @var UserRepository */
    private $userRepository;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(UserRepository $userRepository, CommandBus $commandBus) {
        $this->userRepository = $userRepository;
        $this->commandBus = $commandBus;
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        if ($resource->getResourceClass() == SystemResourceClass::USER) {
            try {
                $user = $this->userRepository->findByUserData($resource);
                $this->commandBus->handle(new UserGrantRolesCommand($user));
            } catch (EntityNotFoundException $e) {
                // not a user, nothing to update
            }
        }
    }
}
