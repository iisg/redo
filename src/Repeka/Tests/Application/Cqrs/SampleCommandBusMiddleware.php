<?php
namespace Repeka\Tests\Application\Cqrs;

use Repeka\Application\Cqrs\Middleware\CommandBusMiddleware;
use Repeka\Domain\Cqrs\Command;

class SampleCommandBusMiddleware implements CommandBusMiddleware {
    public $latestCommand;

    public function handle(Command $command, callable $next) {
        $this->latestCommand = $command;
        $next($command);
    }
}
