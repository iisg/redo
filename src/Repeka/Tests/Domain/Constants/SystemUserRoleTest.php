<?php
namespace Repeka\Tests\Domain\Domain\Constants;

use Repeka\Domain\Constants\SystemUserRole;

class SystemUserRoleTest extends \PHPUnit_Framework_TestCase {
    public function testConvertingToUserRole() {
        $this->assertEquals(SystemUserRole::ADMIN, SystemUserRole::ADMIN()->toUserRole()->getId());
    }
}
