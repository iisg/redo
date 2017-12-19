<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

class ParsingResult {
    /** @var string */
    private $parsed;
    /** @var string */
    private $remaining;

    public function __construct(string $parsed, string $remaining) {
        $this->parsed = $parsed;
        $this->remaining = $remaining;
    }

    public function getParsed(): string {
        return $this->parsed;
    }

    public function getRemaining(): string {
        return $this->remaining;
    }
}
