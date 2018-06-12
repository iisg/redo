<?php
namespace Repeka\Domain\Exception;

class InsufficientPrivilegesException extends DomainException {
    public function __construct(string $errorMessage, \Exception $previous = null) {
        parent::__construct('insufficientPermissions', 403, ['message' => $errorMessage,], $previous, $errorMessage);
    }
}
