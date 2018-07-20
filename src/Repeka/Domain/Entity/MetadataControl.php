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
 * @method static MetadataControl DISPLAY_STRATEGY()
 */
class MetadataControl extends Enum {
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const INTEGER = 'integer';
    const DATE = 'date';
    const BOOLEAN = 'boolean';
    const RELATIONSHIP = 'relationship';
    const FILE = 'file';
    const DISPLAY_STRATEGY = 'display-strategy';
}
