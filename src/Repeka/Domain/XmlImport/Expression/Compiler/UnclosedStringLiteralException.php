<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

class UnclosedStringLiteralException extends ExpressionCompilerException {
    /** @var string */
    private $literal;

    public function __construct(string $literal) {
        parent::__construct('unclosedStringLiteral', ['literal' => $literal]);
        $this->literal = $literal;
    }

    public function getLiteral(): string {
        return $this->literal;
    }
}
