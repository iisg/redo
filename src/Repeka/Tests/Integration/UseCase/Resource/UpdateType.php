<?php
namespace Repeka\Tests\Integration\UseCase\Resource;

use MyCLabs\Enum\Enum;

/**
 * @method static UpdateType APPEND()
 * @method static UpdateType OVERRIDE()
 * @method static UpdateType MOVE_TO_PLACE()
 * @method static UpdateType EXECUTE_TRANSITION()
 */
class UpdateType extends Enum {
    const APPEND = 'append';
    const OVERRIDE = 'override';
    const MOVE_TO_PLACE = 'move_to_place';
    const EXECUTE_TRANSITION = 'execute_transition';
    const RERENDER_DYNAMIC_METADATA = 'rerender_dynamic_metadata';
}
