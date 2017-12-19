<?php
namespace Repeka\Domain\XmlImport\Mapping;

use Repeka\Domain\XmlImport\XmlImportException;

class InvalidMappingException extends XmlImportException {
    /** @var string */
    private $key;

    public function __construct(string $key) {
        parent::__construct('invalidMapping', ['key' => $key]);
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }
}
