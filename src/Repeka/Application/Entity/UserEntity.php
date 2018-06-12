<?php
namespace Repeka\Application\Entity;

use Repeka\Domain\Entity\User;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEntity extends User implements UserInterface, EquatableInterface, \Serializable {
    private $id;

    private $password;

    private $isActive;

    public function __construct() {
        $this->isActive = true;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getSalt(): ?string {
        return null;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array {
        $roles = parent::getRoles();
        $roles[] = 'ROLE_USER';
        // add necessary ROLE_ prefixes to SOME_* roles so they can be used with Symfony's security annotations
        if (in_array('ADMIN_SOME_CLASS', $roles)) {
            $roles[] = 'ROLE_ADMIN_SOME_CLASS';
        }
        if (in_array('OPERATOR_SOME_CLASS', $roles)) {
            $roles[] = 'ROLE_OPERATOR_SOME_CLASS';
        }
        return $roles;
    }

    public function eraseCredentials() {
    }

    public function serialize() {
        return serialize([$this->id, $this->password, $this->roles]);
    }

    public function unserialize($serialized) {
        list ($this->id, $this->password, $this->roles) = unserialize($serialized);
    }

    /**
     * Ensures that the roles for the user are recalculated when they have changed.
     * @see http://stackoverflow.com/a/13837102/878514
     */
    public function isEqualTo(UserInterface $otherUser) {
        if ($otherUser instanceof UserEntity) {
            return count($this->getRoles()) == count($otherUser->getRoles())             // count of roles hasn't changed...
                && count(array_diff($this->getRoles(), $otherUser->getRoles())) == 0;    // ... and there are no new roles
        }
        return false;
    }
}
