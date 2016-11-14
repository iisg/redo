<?php
namespace Repeka\CoreModule\Domain\Cqrs;

use Repeka\CoreModule\Domain\Exception\InvalidCommandException;

interface CommandValidator {
    /**
     * @throws InvalidCommandException when the command has some validation errors
     */
    public function validate(Command $command);
}
