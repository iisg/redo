<?php
namespace Repeka\Domain\Entity;

/**
 * Resource is reserved word in PHP7: http://php.net/manual/en/reserved.other-reserved-words.php
 */
class ResourceEntity {
    private $id;
    private $kind;
    private $marking;
    private $contents;

    public function __construct(ResourceKind $kind, array $contents) {
        $this->kind = $kind;
        $this->contents = $contents;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getKind(): ResourceKind {
        return $this->kind;
    }

    public function getMarking() {
        return $this->marking;
    }

    public function setMarking($marking) {
        $this->marking = $marking;
    }

    public function getContents(): array {
        return $this->contents;
    }

    public function updateContents(array $contents) {
        $this->contents = $contents;
    }
}
