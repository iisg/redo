<?php
namespace Repeka\Tests\Application\Factory;

use Repeka\Application\Factory\UserEntityFactory;

class UserEntityFactoryTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingUser() {
        $username = 'JohnDoe';
        $user = (new UserEntityFactory())->createUser($username);
        $this->assertNotNull($user);
        $this->assertSame($username, $user->getUsername());
        $this->assertEmpty($user->getUserRoles());
    }
}
