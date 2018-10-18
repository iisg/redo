<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class EvaluateDisplayStrategiesOnResourceCreationListener extends CommandEventsListener {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        if ($command->getTransition()->getId() == SystemTransition::CREATE) {
            $this->commandBus->handle(new ResourceEvaluateDisplayStrategiesCommand($command->getResource()));
        }
    }
}
