<?php
namespace Repeka\Domain\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

class BeforeCommandHandlingEvent extends CqrsCommandEvent {
    private $dataForHandledEvent = [];

    public function __construct(Command $command) {
        parent::__construct($command);
    }

    public function replaceCommand(Command $command) {
        $this->command = $command;
    }

    public function setDataForHandledEvent(string $namespace, $data) {
        $this->dataForHandledEvent[$namespace] = $data;
    }

    public function getDataForHandledEvent(): array {
        return $this->dataForHandledEvent;
    }
}
