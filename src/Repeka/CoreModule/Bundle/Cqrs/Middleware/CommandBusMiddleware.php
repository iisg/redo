<?php
namespace Repeka\CoreModule\Bundle\Cqrs\Middleware;

use Repeka\CoreModule\Domain\Cqrs\Command;

interface CommandBusMiddleware {
    public function handle(Command $command, callable $next);
}
