<?php
namespace Repeka\Domain\Exception;

class NotFoundException extends DomainException {
    public function __construct($message, \Exception $previous = null) {
        parent::__construct($message, $previous);
        $this->setCode(404);
    }
}
