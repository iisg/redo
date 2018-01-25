<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepositoryIntegrationTest extends IntegrationTestCase {
    /** @var UserRepository|UserLoaderInterface */
    private $userRepository;

    /** @before */
    public function init() {
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->loadAllFixtures();
    }

    public function testLoadByUsername() {
        $user = $this->userRepository->loadUserByUsername('admin');
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('admin', $user->getUsername());
    }
}
