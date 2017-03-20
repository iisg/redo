<?php
namespace Repeka\Domain\Constants;

use MyCLabs\Enum\Enum;
use ReflectionClass;
use Repeka\Domain\Entity\UserRole;

/**
 * @method static SystemUserRole ADMIN()
 */
class SystemUserRole extends Enum {
    const ADMIN = '11d87f9d-dd56-4ab1-afe8-9d560a8eaa9d';

    public function toUserRole() {
        $userRole = new UserRole([]);
        $this->forceSetId($userRole, $this->value);
        return $userRole;
    }

    private function forceSetId(UserRole $role, string $id) {
        $reflectionClass = new ReflectionClass(get_class($role));
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($role, $id);
    }
}
