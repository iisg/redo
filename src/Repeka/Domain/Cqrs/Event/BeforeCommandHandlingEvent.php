<?php
namespace Repeka\Domain\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

class BeforeCommandHandlingEvent extends CqrsCommandEvent {
    public function __construct(Command $command) {
        parent::__construct($command);
    }

    public function replaceCommand(Command $command) {
        $this->command = $command;
    }
}
