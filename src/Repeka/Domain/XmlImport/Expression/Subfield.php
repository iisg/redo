<?php
namespace Repeka\Domain\XmlImport\Expression;

class Subfield {
    /** @var string */
    private $name;
    /** @var string */
    private $content;

    public function __construct(string $name, string $content) {
        $this->name = $name;
        $this->content = $content;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getContent(): string {
        return $this->content;
    }

    public static function fromDOMNode(\DOMElement $subfieldNode): Subfield {
        if ($subfieldNode->hasAttribute('code')) {
            $name = $subfieldNode->getAttribute('code');
        } else {
            $name = $subfieldNode->tagName;
        }
        $content = $subfieldNode->textContent;
        return new Subfield($name, $content);
    }
}
