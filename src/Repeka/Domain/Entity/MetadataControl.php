<?php
namespace Repeka\Domain\Entity;

use MyCLabs\Enum\Enum;

/**
 * @method static MetadataControl TEXT()
 * @method static MetadataControl TEXTAREA()
 * @method static MetadataControl INTEGER()
 * @method static MetadataControl DATE()
 * @method static MetadataControl BOOLEAN()
 * @method static MetadataControl RELATIONSHIP()
 * @method static MetadataControl FILE()
 */
class MetadataControl extends Enum {
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const INTEGER = 'integer';
    const DATE = 'date';
    const BOOLEAN = 'boolean';
    const RELATIONSHIP = 'relationship';
    const FILE = 'file';

    /** @return string[] */
    public static function all(): array {
        $constantsMap = (new \ReflectionClass(self::class))->getConstants();
        return array_values($constantsMap);
    }
}
