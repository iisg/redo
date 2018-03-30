<?php
namespace Repeka\Application\Cqrs\Event;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\Command;
use Symfony\Component\EventDispatcher\Event;

abstract class CqrsCommandEvent extends Event {
    /** @var Command */
    private $command;

    public function __construct(Command $command) {
        $this->command = $command;
    }

    public function getCommand(): Command {
        return $this->command;
    }

    public function getEventName(): string {
        return self::getEventNameFromClasses(get_class($this), get_class($this->command));
    }

    public static function getEventNameFromClasses(string $eventClass, string $commandClass): string {
        return $eventClass::EVENT_NAME . '.' . AbstractCommand::getCommandNameFromClassName($commandClass);
    }
}
