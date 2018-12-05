<?php
namespace Repeka\Domain\Constants;

use MyCLabs\Enum\Enum;

/**
 * @method static SystemRole ADMIN()
 * @method static SystemRole OPERATOR()
 */
class SystemRole extends Enum {
    const ADMIN = 'admin';
    const OPERATOR = 'operator';

    public function getConfigKey(): string {
        return $this->getValue() . 's';
    }

    /**
     * Creates role with name or without the resource class context.
     *
     * @param null|string $resourceClass
     * @return string role name
     * @example roleName() -> 'ADMIN_SOME_CLASS'
     * @example roleName('foo') -> 'ADMIN-foo'
     */
    public function roleName(?string $resourceClass = null): string {
        return strtoupper($this->getValue()) . ($resourceClass ? '-' . $resourceClass : '_SOME_CLASS');
    }

    /** @return SystemRole[] */
    public function getImpliedRoles(): array {
        return [self::ADMIN => [self::OPERATOR()]][$this->getValue()] ?? [];
    }
}
