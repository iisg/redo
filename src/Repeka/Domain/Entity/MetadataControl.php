<?php
namespace Repeka\Domain\Entity;

use MyCLabs\Enum\Enum;

/**
 * @method static MetadataControl TEXT()
 * @method static MetadataControl TEXTAREA()
 * @method static MetadataControl INTEGER()
 * @method static MetadataControl DOUBLE()
 * @method static MetadataControl FLEXIBLE_DATE()
 * @method static MetadataControl TIMESTAMP()
 * @method static MetadataControl BOOLEAN()
 * @method static MetadataControl RELATIONSHIP()
 * @method static MetadataControl FILE()
 * @method static MetadataControl WYSIWYG_EDITOR()
 * @method static MetadataControl DISPLAY_STRATEGY()
 * @method static MetadataControl SYSTEM_LANGUAGE()
 */
class MetadataControl extends Enum {
    const TEXT = 'text';
    const TEXTAREA = 'textarea';
    const WYSIWYG_EDITOR = 'wysiwyg-editor';
    const INTEGER = 'integer';
    const DOUBLE = 'double';
    const FLEXIBLE_DATE = 'flexible-date';
    const TIMESTAMP = 'timestamp';
    const BOOLEAN = 'boolean';
    const RELATIONSHIP = 'relationship';
    const FILE = 'file';
    const DISPLAY_STRATEGY = 'display-strategy';
    const SYSTEM_LANGUAGE = 'system-language';
}
