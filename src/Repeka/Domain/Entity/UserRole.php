<?php
namespace Repeka\Domain\Entity;

use Repeka\Domain\Constants\SystemUserRole;

class UserRole {
    private $id;
    private $name;

    public function __construct(array $name) {
        $this->name = $name;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getName(): array {
        return $this->name;
    }

    public function update(array $name) {
        $this->name = $name;
    }

    public function isSystemRole(): bool {
        return SystemUserRole::isValid($this->id);
    }
}
