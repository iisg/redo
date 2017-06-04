<?php
namespace Repeka\Domain\Exception;

class InvalidCommandException extends DomainException {
    public function __construct(array $violations, ?\Exception $cause = null, $additionalMessage = '') {
        parent::__construct('Validation of a command failed ' . $additionalMessage, $cause);
        $this->setData($violations);
    }
}
