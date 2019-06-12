<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Symfony\Component\HttpFoundation\RequestStack;

class EventLogCreateCommandAdjuster implements CommandAdjuster {
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    /** @param EventLogCreateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new EventLogCreateCommand(
            $command->getEventName(),
            $command->getEventGroup(),
            $command->getResource(),
            $command->getRequest() ?: $this->requestStack->getCurrentRequest()
        );
    }
}
