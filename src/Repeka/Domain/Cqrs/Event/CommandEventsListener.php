<?php
namespace Repeka\Domain\Cqrs\Event;

use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

abstract class CommandEventsListener {
    /** @inheritdoc */
    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
    }

    /** @inheritdoc */
    public function onCommandError(CommandErrorEvent $event): void {
    }

    /** @inheritdoc */
    public function onCommandHandled(CommandHandledEvent $event): void {
    }

    final public function isSubscribed(CqrsCommandEvent $event): bool {
        return in_array($event->getCommand()->getCommandClassName(), $this->subscribedFor());
    }

    protected function subscribedFor(): array {
        return [ResourceTransitionCommand::class];
    }

    final public function handleCommandEvent(CqrsCommandEvent $event) {
        if ($this->isSubscribed($event)) {
            switch (get_class($event)) {
                case BeforeCommandHandlingEvent::class:
                    $this->onBeforeCommandHandling($event);
                    break;
                case CommandErrorEvent::class:
                    $this->onCommandError($event);
                    break;
                default:
                    $this->onCommandHandled($event);
            }
        }
    }
}
