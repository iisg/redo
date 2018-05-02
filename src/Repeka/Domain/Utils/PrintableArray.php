<?php
namespace Repeka\Domain\Utils;

class PrintableArray extends ImmutableIteratorAggregate {
    public function __toString() {
        return implode(', ', $this->contents);
    }

    public function flatten(): PrintableArray {
        return new self(explode(', ', (string)$this));
    }
}
