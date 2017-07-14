<?php
namespace Repeka\Domain\Exception;

use Repeka\Domain\Cqrs\Command;

class InvalidCommandException extends DomainException {
    public function __construct(Command $command, array $violations, ?\Exception $cause = null, $additionalMessage = '') {
        parent::__construct('Validation of a command ' . $command->getCommandName() . ' failed ' . $additionalMessage, $cause);
        $this->setData($violations);
    }
}
