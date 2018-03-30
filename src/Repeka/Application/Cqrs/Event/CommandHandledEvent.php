<?php
namespace Repeka\Application\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

class CommandHandledEvent extends CqrsCommandEvent {
    const EVENT_NAME = 'command_handled';

    private $result;

    public function __construct(Command $command, $result) {
        parent::__construct($command);
        $this->result = $result;
    }

    public function getResult() {
        return $this->result;
    }
}
