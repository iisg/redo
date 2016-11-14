<?php
namespace Repeka\CoreModule\Bundle\Cqrs;

use Repeka\CoreModule\Bundle\Cqrs\Middleware\CommandBusMiddleware;
use Repeka\CoreModule\Domain\Cqrs\Command;
use Repeka\CoreModule\Domain\Cqrs\CommandBus;

/**
 * This is very similar to https://github.com/SimpleBus/MessageBus but it DOES return the value received from command handler.
 * CQRS tells you not to to that with asynchronous calls in mind. We are very unlikely to have any asynchronous command.
 * @see http://www.blogcoward.com/archive/2011/05/14/cqrs-for-dummies-ndash-example-ndash-when-returning-a-value.aspx
 */
class RepekaCommandBus implements CommandBus {
    /** @var CommandBusMiddleware[] */
    private $middlewares;

    public function __construct(array $middlewares = []) {
        $this->middlewares = $middlewares;
    }

    public function handle(Command $command) {
        $callable = $this->callableForNextMiddleware(0);
        return $callable($command);
    }

    /**
     * C&P from https://goo.gl/i4rkio with return statement added.
     */
    private function callableForNextMiddleware($index): callable {
        if (!isset($this->middlewares[$index])) {
            return function () {
            };
        }
        $middleware = $this->middlewares[$index];
        return function ($command) use ($middleware, $index) {
            return $middleware->handle($command, $this->callableForNextMiddleware($index + 1));
        };
    }
}
