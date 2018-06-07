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
        // TODO gain them based on the rules defined in REPEKA-503
        return ['ROLE_USER', 'ROLE_OPERATOR', 'ROLE_ADMIN'];
    }

    public function eraseCredentials() {
    }

    public function serialize() {
        return serialize(
            [
                $this->id,
                $this->password,
            ]
        );
    }

    public function unserialize($serialized) {
        list (
            $this->id,
            $this->password,
            ) = unserialize($serialized);
    }

    public function isEqualTo(UserInterface $otherUser) {
        if ($otherUser instanceof UserEntity) {
            return $this->getId() == $otherUser->getId();
        }
        return false;
    }
}
