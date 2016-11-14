<?php
namespace Repeka\CoreModule\Tests\Bundle\Cqrs;

use Repeka\CoreModule\Bundle\Cqrs\Middleware\CommandBusMiddleware;
use Repeka\CoreModule\Domain\Cqrs\Command;

class SampleCommandBusMiddleware implements CommandBusMiddleware {
    public $latestCommand;

    public function handle(Command $command, callable $next) {
        $this->latestCommand = $command;
        $next($command);
    }
}
