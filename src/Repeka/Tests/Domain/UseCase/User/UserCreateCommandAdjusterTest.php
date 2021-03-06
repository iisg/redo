<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserCreateCommandAdjuster;

class UserCreateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    /** @var UserCreateCommandAdjuster */
    private $adjuster;

    /** @before */
    public function init() {
        $this->adjuster = new UserCreateCommandAdjuster($this->createMock(ResourceContentsAdjuster::class));
    }

    public function testChangingUsernameToLower() {
        $command = new UserCreateCommand("ADmIN123");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals("admin123", $preparedCommand->getUsername());
    }

    public function testRemovingInvalidCharactersLower() {
        $command = new UserCreateCommand("AD'mIN\"12**3");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals("admin123", $preparedCommand->getUsername());
    }

    public function testAcceptsPkWeirdUsernames() {
        $command = new UserCreateCommand("b/123");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals("b/123", $preparedCommand->getUsername());
    }

    public function testAcceptingEmailAddressAsUsername() {
        $command = new UserCreateCommand("repeka@local.pl");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals("repeka@local.pl", $preparedCommand->getUsername());
    }

    public function testAcceptingEmailAddressWithPlusAsUsername() {
        $command = new UserCreateCommand("repeka+a@local.pl");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals("repeka+a@local.pl", $preparedCommand->getUsername());
    }

    public function testAcceptingEmailAddressWithDashAsUsername() {
        $command = new UserCreateCommand("repeka-a@local.pl");
        /** @var UserCreateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals("repeka-a@local.pl", $preparedCommand->getUsername());
    }
}
