<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemUserRole;

class UserRole implements Identifiable {
    private $id;
    private $name;

    public function __construct(array $name) {
        $this->name = $name;
    }

    public function getId(): int {
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

    public function toSystemRole(): SystemUserRole {
        Assertion::true($this->isSystemRole());
        return new SystemUserRole($this->id);
    }
}
