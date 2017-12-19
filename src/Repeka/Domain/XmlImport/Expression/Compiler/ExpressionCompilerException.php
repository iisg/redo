<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

use Repeka\Domain\XmlImport\XmlImportException;

class ExpressionCompilerException extends XmlImportException {
    /** @var string */
    private $mappingKey;

    public function getMappingKey(): string {
        return $this->mappingKey;
    }

    public function setMappingKey(string $mappingKey) {
        $this->mappingKey = $mappingKey;
        $this->params['mappingKey'] = $mappingKey;
    }
}
