<?php
namespace Repeka\CoreModule\Domain\Exception;

class InvalidCommandException extends DomainException {
    public function __construct(array $violations, \Exception $cause) {
        parent::__construct('Validation of a command failed.', $cause);
        $this->setData($violations);
    }
}
