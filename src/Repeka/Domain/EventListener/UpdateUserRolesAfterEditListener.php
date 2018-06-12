<?php
namespace Repeka\Domain\EventListener;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\User\UserGrantRolesCommand;

class UpdateUserRolesAfterEditListener {
    use CommandBusAware;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function onResourceTransition(CommandHandledEvent $event) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        if ($resource->getResourceClass() == SystemResourceClass::USER) {
            try {
                $user = $this->userRepository->findByUserData($resource);
                $this->handleCommand(new UserGrantRolesCommand($user));
            } catch (EntityNotFoundException $e) {
                // not a user, nothing to update
            }
        }
    }
}
