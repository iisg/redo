<?php
namespace Repeka\Domain\Exception;

class InsufficientUserRolesException extends DomainException {
    public function __construct(string $message, \Exception $previous = null) {
        parent::__construct($message, $previous);
        $this->setCode(403);
    }
}
