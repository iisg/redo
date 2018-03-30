<?php
namespace Repeka\Application\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

class BeforeCommandHandlingEvent extends CqrsCommandEvent {
    const EVENT_NAME = 'command_before';

    public function __construct(Command $command) {
        parent::__construct($command);
    }
}
