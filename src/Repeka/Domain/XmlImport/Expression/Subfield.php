<?php
namespace Repeka\Domain\XmlImport\Expression;

use Assert\Assertion;

class Subfield {
    /** @var string */
    private $name;
    /** @var string */
    private $content;

    public function __construct(string $name, string $content) {
        Assertion::regex($name, '/^[*a-z0-9]$/');
        $this->name = $name;
        $this->content = $content;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getContent(): string {
        return $this->content;
    }

    public static function fromDOMNode(\DOMNode $subfieldNode): Subfield {
        $name = $subfieldNode->attributes->getNamedItem('code')->textContent;
        $content = $subfieldNode->textContent;
        return new Subfield($name, $content);
    }
}
