<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\Application;

use Repeka\CoreModule\Application\Command\Command;

interface CommandBus {
    public function handle(Command $command);
}
