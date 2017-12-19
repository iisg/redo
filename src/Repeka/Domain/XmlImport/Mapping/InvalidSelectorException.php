<?php
namespace Repeka\Domain\XmlImport\Mapping;

use Repeka\Domain\XmlImport\XmlImportException;

class InvalidSelectorException extends XmlImportException {
    /** @var string */
    private $selector;
    /** @var string */
    private $key;

    public function __construct(string $key, string $selector) {
        parent::__construct('invalidSelector', ['mappingKey' => $key, 'selector' => $selector]);
        $this->key = $key;
        $this->selector = $selector;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function getSelector(): string {
        return $this->selector;
    }
}
