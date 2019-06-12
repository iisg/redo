<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Entity\EventLogEntry;
use Repeka\Domain\Repository\EventLogRepository;
use Repeka\Domain\Service\TimeProvider;

class EventLogCreateCommandHandler {

    /** @var EventLogRepository */
    private $eventLogRepository;
    /** @var TimeProvider */
    private $timeProvider;

    public function __construct(EventLogRepository $eventLogRepository, TimeProvider $timeProvider) {
        $this->eventLogRepository = $eventLogRepository;
        $this->timeProvider = $timeProvider;
    }

    public function handle(EventLogCreateCommand $command): EventLogEntry {
        $eventLogEntry = new EventLogEntry(
            $command->getEventName(),
            $command->getEventGroup(),
            $command->getResource(),
            $command->getRequest(),
            $this->timeProvider
        );
        return $this->eventLogRepository->save($eventLogEntry);
    }
}
