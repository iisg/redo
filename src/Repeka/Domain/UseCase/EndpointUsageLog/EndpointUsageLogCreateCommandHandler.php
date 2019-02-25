<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

use Repeka\Domain\Entity\EndpointUsageLogEntry;
use Repeka\Domain\Repository\EndpointUsageLogRepository;

class EndpointUsageLogCreateCommandHandler {

    /** @var EndpointUsageLogRepository */
    private $endpointUsageLogRepository;

    public function __construct(EndpointUsageLogRepository $endpointUsageLogRepository) {
        $this->endpointUsageLogRepository = $endpointUsageLogRepository;
    }

    public function handle(EndpointUsageLogCreateCommand $command): EndpointUsageLogEntry {
        $endpointUsageLog = new EndpointUsageLogEntry(
            $command->getRequest(),
            $command->getEndpointUsageTrackingKey(),
            $command->getResource()
        );
        return $this->endpointUsageLogRepository->save($endpointUsageLog);
    }
}
