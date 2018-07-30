<?php
namespace Repeka\Domain\Exception;

class InvalidResourceDisplayStrategyException extends DomainException {
    public function __construct(string $errorMessage, \Throwable $previous = null) {
        parent::__construct('invalidResourceDisplayStrategy', 400, ['message' => $errorMessage,], $previous, $errorMessage);
    }
}
