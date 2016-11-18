<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Exception\InvalidCommandException;

interface CommandValidator {
    /**
     * @throws InvalidCommandException when the command has some validation errors
     */
    public function validate(Command $command);
}
