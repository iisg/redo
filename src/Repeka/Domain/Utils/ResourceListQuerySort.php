<?php
namespace Repeka\Domain\Utils;

use Repeka\Domain\Entity\Metadata;

class ResourceListQuerySort extends ImmutableIteratorAggregate {
    public function __construct($columnId, string $direction) {
        parent::__construct(['columnId' => $columnId instanceof Metadata ? $columnId->getId() : $columnId, 'direction' => $direction]);
    }

    /** @param string|int|Metadata $metadata */
    public static function asc($metadata): self {
        return new self($metadata, 'ASC');
    }

    /** @param string|int|Metadata $metadata */
    public static function desc($metadata): self {
        return new self($metadata, 'DESC');
    }

    public static function idAsc(): self {
        return self::asc('id');
    }

    public static function idDesc(): self {
        return self::desc('id');
    }
}
