<?php
namespace Repeka\Domain\Exception;

class NotFoundException extends DomainException {
    public function __construct(string $errorMessageId, array $params = []) {
        parent::__construct($errorMessageId, 404, $params);
    }
}
