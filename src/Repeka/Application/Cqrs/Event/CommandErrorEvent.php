<?php
namespace Repeka\Application\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

class CommandErrorEvent extends CqrsCommandEvent {
    const EVENT_NAME = 'command_error';

    /** @var \Exception */
    private $exception;

    public function __construct(Command $command, \Exception $exception) {
        parent::__construct($command);
        $this->exception = $exception;
    }

    public function getException(): \Exception {
        return $this->exception;
    }
}
