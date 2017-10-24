<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Doctrine\Common\Collections\ArrayCollection;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\Validation\Rules\UserRoleHasNoUsageRule;

class UserRoleHasNoUsageRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var UserRoleRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $userRoleRepository;
    /** @var UserRoleHasNoUsageRule */
    private $rule;

    protected function setUp() {
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $this->rule = new UserRoleHasNoUsageRule($this->userRoleRepository);
    }

    public function testPositive() {
        $dummy = $this->createMock(UserRole::class);
        $dummy->method('getUsers')->willReturn(new ArrayCollection());
        $this->userRoleRepository->expects($this->once())->method('findOne')->willReturn($dummy);
        $result = $this->rule->validate($dummy);
        $this->assertTrue($result);
    }

    public function testNegative() {
        $dummy = $this->createMock(UserRole::class);
        $user = $this->createMock(UserEntity::class);
        $dummy->method('getUsers')->willReturn(new ArrayCollection([$user]));
        $this->userRoleRepository->expects($this->once())->method('findOne')->willReturn($dummy);
        $result = $this->rule->validate($dummy);
        $this->assertFalse($result);
    }
}
