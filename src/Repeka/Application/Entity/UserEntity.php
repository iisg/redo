<?php
namespace Repeka\Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\UserRole;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEntity extends User implements UserInterface, EquatableInterface, \Serializable {
    private $id;

    private $username;

    private $password;

    private $email;

    private $isActive;

    /** Precalculated user roles (strings) for Symfony security purposes. */
    private $roles;

    /**
     * OneToMany with custom UserRoles.
     * @var ArrayCollection|UserRole[]
     */
    private $userRoles;

    public function __construct() {
        $this->isActive = true;
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): User {
        $this->username = $username;
        return $this;
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
        if (!$this->roles) {
            $this->roles = ['ROLE_USER'];
            foreach (SystemUserRole::values() as $systemRole) {
                if ($this->hasRole($systemRole->toUserRole())) {
                    $this->roles[] = 'ROLE_' . $systemRole->getKey();
                }
            }
        }
        return $this->roles;
    }

    public function eraseCredentials() {
    }

    public function serialize() {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->roles,
        ]);
    }

    public function unserialize($serialized) {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->roles,
            ) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * Ensures that the roles for the user are recalculated when they have changed.
     * @see http://stackoverflow.com/a/13837102/878514
     */
    public function isEqualTo(UserInterface $user) {
        if ($user instanceof UserEntity) {
            return count($this->getRoles()) == count($user->getRoles()) && count(array_diff($this->getRoles(), $user->getRoles())) == 0;
        }
        return false;
    }

    /** @param UserRole[] $roles */
    public function updateRoles(array $roles): void {
        $this->userRoles->clear();
        foreach ($roles as $role) {
            $this->userRoles->add($role);
        }
        $this->roles = null;
    }

    public function getUserRoles(): array {
        return $this->userRoles->toArray();
    }
}
