<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Entity\EventLogEntry;
use Repeka\Domain\Repository\EventLogRepository;

class EventLogCreateCommandHandler {

    /** @var EventLogRepository */
    private $eventLogRepository;

    public function __construct(EventLogRepository $eventLogRepository) {
        $this->eventLogRepository = $eventLogRepository;
    }

    public function handle(EventLogCreateCommand $command): EventLogEntry {
        $eventLogEntry = new EventLogEntry(
            $command->getEventName(),
            $command->getEventGroup(),
            $command->getResource(),
            $command->getRequest()
        );
        return $this->eventLogRepository->save($eventLogEntry);
    }
}
