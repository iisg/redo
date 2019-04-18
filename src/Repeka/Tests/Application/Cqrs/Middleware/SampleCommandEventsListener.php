<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandErrorEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\UseCase\Resource\ResourceQuery;

class SampleCommandEventsListener extends CommandEventsListener {
    public $before = [];
    /** @var CommandHandledEvent[] */
    public $handled = [];
    public $error = [];

    /** @var Command */
    public $commandToReplace;
    public $dataToSet;

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        $this->before[] = $event;
        if ($this->commandToReplace) {
            $event->replaceCommand($this->commandToReplace);
            $this->commandToReplace = null;
        }
        if ($this->dataToSet) {
            $event->setDataForHandledEvent(self::class, $this->dataToSet);
        }
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $this->handled[] = $event;
    }

    public function onCommandError(CommandErrorEvent $event): void {
        $this->error[] = $event;
    }

    protected function subscribedFor(): array {
        return [ResourceQuery::class];
    }
}
