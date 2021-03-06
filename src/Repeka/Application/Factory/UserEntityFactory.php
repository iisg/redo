<?php
namespace Repeka\Application\Factory;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Factory\UserFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserEntityFactory extends UserFactory {
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        ResourceKindRepository $resourceKindRepository,
        CommandBus $commandBus
    ) {
        parent::__construct($resourceKindRepository, $commandBus);
        $this->passwordEncoder = $passwordEncoder;
    }

    /** @return UserEntity */
    protected function createApplicationUser(string $username, ?string $plainPassword, ResourceEntity $userData): User {
        $user = new UserEntity();
        $user->setUserData($userData);
        $user->setUsername($username);
        if ($plainPassword) {
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
        }
        return $user;
    }

    public function updatePassword(User $user, string $plainPassword): User {
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);
        return $user;
    }
}
