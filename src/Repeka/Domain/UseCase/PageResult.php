<?php
namespace Repeka\Domain\UseCase;

class PageResult implements \IteratorAggregate, \ArrayAccess, \Countable {
    /** @var array */
    private $results;
    /** @var int */
    private $totalCount;

    public function __construct(array $results = [], int $totalCount = 0) {
        $this->results = $results;
        $this->totalCount = $totalCount;
    }

    public function getResults(): array {
        return $this->results;
    }

    public function getTotalCount(): int {
        return $this->totalCount;
    }

    public function getIterator() {
        return new \ArrayIterator($this->results);
    }

    public function offsetExists($offset) {
        return isset($this->results[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->results[$offset]) ? $this->results[$offset] : null;
    }

    /** @inheritdoc */
    public function offsetSet($offset, $value) {
        throw new \LogicException('PageResult is immutable.');
    }

    /** @inheritdoc */
    public function offsetUnset($offset) {
        throw new \LogicException('PageResult is immutable.');
    }

    public function count() {
        return count($this->results);
    }
}
