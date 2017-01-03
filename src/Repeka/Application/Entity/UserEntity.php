<?php
namespace Repeka\Application\Entity;

use Repeka\Domain\Entity\User;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEntity implements User, UserInterface, EquatableInterface, \Serializable {
    private $id;

    private $username;

    private $password;

    private $email;

    private $firstname;

    private $lastname;

    private $isActive;

    private $staticPermissions;

    private $roles;

    public function __construct() {
        $this->isActive = true;
        $this->staticPermissions = [];
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

    public function getFirstname(): string {
        return $this->firstname;
    }

    public function setFirstname(string $firstname) {
        $this->firstname = $firstname;
    }

    public function getLastname(): string {
        return $this->lastname;
    }

    public function setLastname(string $lastname) {
        $this->lastname = $lastname;
    }

    public function getSalt() {
        return null;
    }

    public function getPassword() {
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
            $staticPermissionRoles = array_map(function ($permission) {
                return 'ROLE_STATIC_' . $permission;
            }, $this->staticPermissions);
            $this->roles = array_merge($staticPermissionRoles, ['ROLE_USER']);
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

    public function updateStaticPermissions(array $permissions) {
        $this->staticPermissions = $permissions;
        $this->roles = null;
    }

    public function getStaticPermissions(): array {
        return $this->staticPermissions;
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
}
