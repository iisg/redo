<?php
namespace Repeka\CoreModule\Bundle\Cqrs\Middleware;

use Repeka\CoreModule\Domain\Cqrs\Command;
use Repeka\CoreModule\Domain\Cqrs\CommandValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidateCommandMiddleware implements CommandBusMiddleware {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function handle(Command $command, callable $next) {
        if ($this->container->has($this->getValidatorId($command))) {
            $this->validate($command);
        }
        return $next($command);
    }

    private function getValidatorId(Command $command) {
        return 'command_validator.' . $command->getCommandName();
    }

    private function validate(Command $command) {
        /** @var CommandValidator $validator */
        $validator = $this->container->get($this->getValidatorId($command));
        $validator->validate($command);
    }
}
