<?php
namespace Repeka\Tests\Application\Factory;

use Repeka\Application\Factory\UserEntityFactory;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceKindRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserEntityFactoryTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingUser() {
        $username = 'JohnDoe';
        $password = 'JohnDoe';
        $resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $commandBus = $this->createMock(CommandBus::class);
        $commandBus->method('handle')->willReturn($this->createMock(ResourceEntity::class));
        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $userEntityFactory = new UserEntityFactory($passwordEncoder, $resourceKindRepository, $commandBus);
        $user = ($userEntityFactory)->createUser($username, $password, ResourceContents::empty());
        $this->assertNotNull($user);
    }
}
