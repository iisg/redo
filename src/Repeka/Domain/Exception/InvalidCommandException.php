<?php
namespace Repeka\Domain\Exception;

use Repeka\Domain\Cqrs\Command;

class InvalidCommandException extends DomainException {
    /** @var Command */
    private $command;
    /** @var array */
    private $violations;

    public function __construct(Command $command, array $violations, string $message, \Exception $previous = null) {
        parent::__construct('invalidCommand', 400, [
            'command' => $command->getCommandName(),
            'violations' => $violations,
        ], $previous, $message);
        $this->command = $command;
        $this->violations = $violations;
    }

    public function getCommand(): Command {
        return $this->command;
    }

    public function getViolations(): array {
        return $this->violations;
    }
}
