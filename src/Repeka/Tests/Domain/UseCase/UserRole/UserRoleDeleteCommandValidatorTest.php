<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\UseCase\UserRole\UserRoleDeleteCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleDeleteCommandValidator;
use Repeka\Domain\Validation\Rules\UserRoleHasNoUsageRule;

class UserRoleDeleteCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var UserRoleHasNoUsageRule|\PHPUnit_Framework_MockObject_MockObject */
    private $userRoleHasNoUsageRule;
    /** @var UserRoleDeleteCommandValidator */
    private $validator;

    protected function setUp() {
        $this->userRoleHasNoUsageRule = $this->createMock(UserRoleHasNoUsageRule::class);
        $this->validator = new UserRoleDeleteCommandValidator($this->userRoleHasNoUsageRule);
    }

    public function testPositive() {
        $this->userRoleHasNoUsageRule->expects($this->once())->method('validate')->willReturn(true);
        $command = new UserRoleDeleteCommand($this->createMock(UserRole::class));
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testNegative() {
        $this->userRoleHasNoUsageRule->expects($this->once())->method('validate')->willReturn(false);
        $command = new UserRoleDeleteCommand($this->createMock(UserRole::class));
        $this->assertFalse($this->validator->isValid($command));
    }
}
