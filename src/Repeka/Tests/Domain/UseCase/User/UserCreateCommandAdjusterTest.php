<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserCreateCommandAdjuster;

class UserCreateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    public function testChangingUsernameToLower() {
        $adjuster = new UserCreateCommandAdjuster();
        $command = new UserCreateCommand("ADmIN123");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $adjuster->adjustCommand($command);
        $this->assertEquals("admin123", $preparedCommand->getUsername());
    }

    public function testRemovingInvalidCharactersLower() {
        $adjuster = new UserCreateCommandAdjuster();
        $command = new UserCreateCommand("AD'mIN\"12**3");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $adjuster->adjustCommand($command);
        $this->assertEquals("admin123", $preparedCommand->getUsername());
    }

    public function testAcceptsPkWeirdUsernames() {
        $adjuster = new UserCreateCommandAdjuster();
        $command = new UserCreateCommand("b/123");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $adjuster->adjustCommand($command);
        $this->assertEquals("b/123", $preparedCommand->getUsername());
    }
}
