<?php
namespace Repeka\Domain\Cqrs\Event;

use Repeka\Domain\Cqrs\Command;

abstract class CqrsCommandEvent {
    /** @var Command */
    protected $command;

    public function __construct(Command $command) {
        $this->command = $command;
    }

    public function getCommand(): Command {
        return $this->command;
    }
}
