<?php
namespace Repeka\Domain\XmlImport;

use Repeka\Domain\Exception\DomainException;

class XmlImportException extends DomainException {
    public function __construct(string $errorMessageId, array $params = []) {
        parent::__construct($errorMessageId, 400, $params);
    }
}
