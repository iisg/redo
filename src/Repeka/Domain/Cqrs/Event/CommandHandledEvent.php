<?php
namespace Repeka\Domain\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

class CommandHandledEvent extends CqrsCommandEvent {
    private $result;

    public function __construct(Command $command, $result) {
        parent::__construct($command);
        $this->result = $result;
    }

    public function getResult() {
        return $this->result;
    }
}
