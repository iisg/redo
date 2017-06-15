<?php
namespace Repeka\Domain\Constants;

use MyCLabs\Enum\Enum;
use Repeka\Application\Entity\EntityUtils;
use Repeka\Domain\Entity\UserRole;

/**
 * @method static SystemUserRole ADMIN()
 * @method static SystemUserRole OPERATOR()
 */
class SystemUserRole extends Enum {
    const ADMIN = '11d87f9d-dd56-4ab1-afe8-9d560a8eaa9d';
    const OPERATOR = 'c4bde879-afaf-4500-ba43-97451932c964';

    public function toUserRole() {
        $userRole = new UserRole([]);
        EntityUtils::forceSetId($userRole, $this->value);
        return $userRole;
    }

    /** @return SystemUserRole[] */
    public function getImpliedRoles(): array {
        return [
            self::ADMIN => [self::OPERATOR()]
        ][$this->getValue()] ?? [];
    }
}
