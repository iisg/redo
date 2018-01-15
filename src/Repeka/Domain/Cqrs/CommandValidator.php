<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Exception\InvalidCommandException;

abstract class CommandValidator {
    public function prepareCommand(Command $command): Command {
        return $command;
    }

    /**
     * @throws InvalidCommandException when the command has some validation errors
     */
    abstract public function validate(Command $command);
}
