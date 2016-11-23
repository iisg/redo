<?php
namespace Repeka\Domain\Exception;

class EntityNotFoundException extends DomainException {
    public function __construct(string $message = '', \Exception $previous = null) {
        parent::__construct($message, $previous);
        $this->setCode(404);
    }
}
