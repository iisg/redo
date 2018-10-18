<?php
namespace Repeka\Domain\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

class CommandHandledEvent extends CqrsCommandEvent {
    private $result;
    private $dataFromBeforeEvent;

    public function __construct(Command $command, $result, array $dataFromBeforeEvent) {
        parent::__construct($command);
        $this->result = $result;
        $this->dataFromBeforeEvent = $dataFromBeforeEvent;
    }

    public function getResult() {
        return $this->result;
    }

    public function getDataFromBeforeEvent(string $namespace) {
        return $this->dataFromBeforeEvent[$namespace] ?? null;
    }
}
