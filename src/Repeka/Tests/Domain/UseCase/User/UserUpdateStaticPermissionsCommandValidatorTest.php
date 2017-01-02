<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\UseCase\User\UserUpdateStaticPermissionsCommand;
use Repeka\Domain\UseCase\User\UserUpdateStaticPermissionsCommandValidator;

class UserUpdateStaticPermissionsCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var UserUpdateStaticPermissionsCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new UserUpdateStaticPermissionsCommandValidator(['A', 'B']);
    }

    public function testValidCommand() {
        $command = new UserUpdateStaticPermissionsCommand(1, ['A']);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testValidCommandIfNoPermissions() {
        $command = new UserUpdateStaticPermissionsCommand(1, []);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidValidIfIdLessThanOrEqual0() {
        $command = new UserUpdateStaticPermissionsCommand(0, ['A']);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidIfNotSupportedPermission() {
        $command = new UserUpdateStaticPermissionsCommand(1, ['X']);
        $this->assertFalse($this->validator->isValid($command));
    }
}
