<?php
namespace Repeka\Domain\Exception;

use Repeka\Domain\Cqrs\Command;

class InvalidCommandException extends DomainException {
    /** @var Command */
    private $command;
    /** @var array */
    private $violations;

    public function __construct(
        ?Command $command = null,
        array $violations,
        string $message,
        \Exception $previous = null,
        int $status = 400
    ) {
        parent::__construct(
            'invalidCommand',
            $status,
            [
                'command' => $command ? $command->getCommandName() : '',
                'violations' => $violations,
            ],
            $previous,
            $message
        );
        $this->command = $command;
        $this->violations = $violations;
    }

    public function getViolations(): array {
        return $this->violations;
    }
}
