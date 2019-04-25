<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;

class ResourceListQuerySort extends AbstractListQueryBuilder {
    /** @var string */
    private $columnId;
    /** @var string */
    private $direction;

    private function __construct($columnId, string $direction) {
        $this->columnId = $columnId instanceof Metadata ? $columnId->getId() : $columnId;
        $this->direction = $direction;
    }

    public function toArray(): array {
        return ['columnId' => $this->columnId, 'direction' => $this->direction];
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
