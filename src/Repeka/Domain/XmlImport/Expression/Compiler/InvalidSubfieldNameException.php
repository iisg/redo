<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

class InvalidSubfieldNameException extends ExpressionCompilerException {
    /** @var string */
    private $subfieldName;

    public function __construct(string $subfieldName) {
        parent::__construct("invalidSubfieldName", ['subfield' => $subfieldName]);
        $this->subfieldName = $subfieldName;
    }

    public function getSubfieldName(): string {
        return $this->subfieldName;
    }
}
