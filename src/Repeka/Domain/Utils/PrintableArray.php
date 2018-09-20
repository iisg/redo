<?php
namespace Repeka\Domain\Utils;

class PrintableArray extends ImmutableIteratorAggregate {
    public function __toString() {
        return implode(', ', $this->contents);
    }

    public function flatten(): PrintableArray {
        return count($this) > 0 ? new self(array_filter(explode(', ', (string)$this))) : new self([]);
    }

    public function offsetExists($offset) {
        return true;
    }

    public function offsetGet($offset) {
        return $this->contents[$offset] ?? '';
    }
}
