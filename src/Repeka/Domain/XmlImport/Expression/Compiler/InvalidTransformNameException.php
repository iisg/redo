<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

class InvalidTransformNameException extends ExpressionCompilerException {
    /** @var string */
    private $transformName;

    public function __construct(string $transformName) {
        parent::__construct('invalidTransformName', ['transform' => $transformName]);
        $this->transformName = $transformName;
    }

    public function getTransformName(): string {
        return $this->transformName;
    }
}
