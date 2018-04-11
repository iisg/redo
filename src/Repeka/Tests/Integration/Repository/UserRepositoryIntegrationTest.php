<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

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

    public function testFindGroups() {
        $admin = $this->getAdminUser();
        $groupKind = $this->handleCommand(
            new ResourceKindCreateCommand(['PL' => 'Grupa', 'EN' => 'Group'], [['id' => SystemMetadata::GROUP_MEMBER]])
        );
        $contents = ResourceContents::fromArray([SystemMetadata::GROUP_MEMBER => $admin->getUserData()]);
        $group1 = $this->handleCommand(new ResourceCreateCommand($groupKind, $contents));
        $group2 = $this->handleCommand(new ResourceCreateCommand($groupKind, ResourceContents::empty()));
        $groups = $this->userRepository->findUserGroups($admin);
        $this->assertContains($group1, $groups);
        $this->assertNotContains($group2, $groups);
    }
}
