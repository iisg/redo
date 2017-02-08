<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandValidator;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidateCommandMiddleware implements CommandBusMiddleware {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function handle(Command $command, callable $next) {
        if (!($command instanceof NonValidatedCommand)) {
            $validatorId = $this->getValidatorId($command);
            if ($this->container->has($validatorId)) {
                $this->validate($command);
            } else {
                throw new \InvalidArgumentException("Could not find a validator for the {$command->getCommandName()}. "
                    . "Looking for the {$validatorId}. "
                    . "If the command is not meant to have a validator, it must extend the NonValidatedCommand class.");
            }
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
