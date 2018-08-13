<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;

/**
 * @method static SystemResourceClass USER()
 */
class SystemResourceClass extends Enum {
    const USER = "users";

    public static function toSystemResourceClassConfig($value) {
        $systemResourceClassConfig = null;
        if ($value == self::USER) {
            $systemResourceClassConfig = ['name' => $value, 'icon' => 'book'];
        }
        /** @noinspection PhpUndefinedVariableInspection */
        Assertion::notNull($systemResourceClassConfig, "Not implemented: resourceClass for value $value");
        return $systemResourceClassConfig;
    }
}
