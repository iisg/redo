<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Domain\Cqrs\Command;

interface CommandBusMiddleware {
    public function handle(Command $command, callable $next);
}
