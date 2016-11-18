<?php
namespace Repeka\Domain\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, \Serializable {
    private $id;

    private $username;

    private $password;

    private $email;

    private $name;

    private $surname;

    private $isActive;

    public function __construct() {
        $this->isActive = true;
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

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getSurname(): string {
        return $this->surname;
    }

    public function setSurname(string $surname) {
        $this->surname = $surname;
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

    public function getRoles() {
        return ['ROLE_USER'];
    }

    public function eraseCredentials() {
    }

    /** @see \Serializable::serialize() */
    public function serialize() {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized) {
        list (
            $this->id,
            $this->username,
            $this->password,
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
}
