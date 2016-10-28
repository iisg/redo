<?php
namespace Repeka\UserModule\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="`user`")
 * @ORM\Entity(repositoryClass="Repeka\UserModule\Bundle\Entity\UserRepository")
 */
class User implements UserInterface, \Serializable {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    public function __construct() {
        $this->isActive = true;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
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
