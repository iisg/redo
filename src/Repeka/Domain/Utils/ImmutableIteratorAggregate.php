<?php
namespace Repeka\Domain\Utils;

use Assert\Assertion;

class ImmutableIteratorAggregate implements \IteratorAggregate, \ArrayAccess {
    /** @var array */
    protected $contents = [];

    public function __construct(array $contents) {
        $this->contents = $contents;
    }

    public function getIterator() {
        return new \ArrayIterator($this->contents);
    }

    public function offsetExists($offset) {
        return isset($this->contents[$offset]);
    }

    public function offsetGet($offset) {
        Assertion::keyIsset($this->contents, $offset);
        return $this->contents[$offset];
    }

    /** @inheritdoc */
    public function offsetSet($offset, $value) {
        throw new \LogicException('Immutable!');
    }

    /** @inheritdoc */
    public function offsetUnset($offset) {
        throw new \LogicException('Immutable!');
    }
}
