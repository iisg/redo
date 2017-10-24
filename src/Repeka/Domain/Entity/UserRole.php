<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemUserRole;

class UserRole implements Identifiable {
    private $id;
    private $name;
    /**
     * ManyToMany with UserEntity.
     * @var ArrayCollection|UserEntity[]
     */
    private $users;

    public function __construct(array $name) {
        $this->name = $name;
        $this->users = new ArrayCollection();
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

    public function getUsers(): Collection {
        return $this->users;
    }
}
