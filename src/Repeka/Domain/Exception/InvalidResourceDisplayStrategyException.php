<?php
namespace Repeka\Domain\Exception;

class InvalidResourceDisplayStrategyException extends DomainException {
    /** @var string */
    private $errorMessage;

    public function __construct(string $errorMessage, \Throwable $previous = null) {
        parent::__construct('invalidResourceDisplayStrategy', 400, ['message' => $errorMessage], $previous, $errorMessage);
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage(): string {
        return $this->message;
    }
}
