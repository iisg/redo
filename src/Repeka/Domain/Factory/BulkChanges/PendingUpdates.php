<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Repeka\Domain\Utils\ImmutableIteratorAggregate;

class PendingUpdates extends ImmutableIteratorAggregate implements \JsonSerializable {

    public static function fromArray($updates): PendingUpdates {
        return new self($updates);
    }

    public static function empty(): PendingUpdates {
        return new self([]);
    }

    public function addUpdate(array $change): PendingUpdates {
        $this->contents[] = $change;
        return $this;
    }

    public function shiftUpdate(): ?array {
        return array_shift($this->contents);
    }

    public function jsonSerialize() {
        return $this->toArray();
    }
}
