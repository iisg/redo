<?php
namespace Repeka\Domain\Constants;

use MyCLabs\Enum\Enum;

/**
 * @method static TaskStatus OWN()
 * @method static TaskStatus POSSIBLE()
 */
class TaskStatus extends Enum {
    const OWN = 'own';
    const POSSIBLE = 'possible';
}
