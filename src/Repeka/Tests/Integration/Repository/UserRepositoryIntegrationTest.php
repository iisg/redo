<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/** @small */
class UserRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var UserRepository|UserLoaderInterface */
    private $userRepository;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->userRepository = $this->container->get(UserRepository::class);
    }

    public function testLoadByUsername() {
        $user = $this->userRepository->loadUserByUsername('admin');
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('admin', $user->getUsername());
    }
}
