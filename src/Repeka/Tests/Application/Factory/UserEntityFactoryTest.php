<?php
namespace Repeka\Tests\Application\Factory;

use Repeka\Application\Factory\UserEntityFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandHandler;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserEntityFactoryTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingUser() {
        $username = 'JohnDoe';
        $password = 'JohnDoe';
        $email = 'JohnDoe';
        $resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $resourceCreateCommandHandler = $this->createMock(ResourceCreateCommandHandler::class);
        $passwordEncoder = $this->createMock(UserPasswordEncoder::class);
        $user = (new UserEntityFactory($passwordEncoder, $resourceKindRepository, $resourceCreateCommandHandler))
            ->createUser($username, $password, $email);
        $this->assertNotNull($user);
        $this->assertSame($username, $user->getUsername());
        $this->assertEmpty($user->getUserRoles());
    }
}
