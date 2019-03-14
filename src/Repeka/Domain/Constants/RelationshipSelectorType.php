<?php
namespace Repeka\Domain\Constants;

use MyCLabs\Enum\Enum;

/**
 * @method static RelationshipSelectorType SIMPLE()
 * @method static RelationshipSelectorType TREE()
 */
class RelationshipSelectorType extends Enum {
    const SIMPLE = 'simple';
    const TREE = 'tree';
}
