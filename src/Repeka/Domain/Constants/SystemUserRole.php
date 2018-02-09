<?php
namespace Repeka\Domain\Constants;

use MyCLabs\Enum\Enum;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\UserRole;

/**
 * @method static SystemUserRole ADMIN()
 * @method static SystemUserRole OPERATOR()
 */
class SystemUserRole extends Enum {
    const ADMIN = -1;
    const OPERATOR = -2;

    public function toUserRole() {
        $userRole = new UserRole([]);
        EntityUtils::forceSetId($userRole, $this->value);
        return $userRole;
    }

    /** @return SystemUserRole[] */
    public function getImpliedRoles(): array {
        return [
                self::ADMIN => [self::OPERATOR()],
            ][$this->getValue()] ?? [];
    }
}
