<?php
namespace Repeka\Application\Factory;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Factory\UserFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandHandler;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserEntityFactory extends UserFactory {
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        ResourceKindRepository $resourceKindRepository,
        ResourceCreateCommandHandler $resourceCreateCommandHandler
    ) {
        parent::__construct($resourceKindRepository, $resourceCreateCommandHandler);
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
}
