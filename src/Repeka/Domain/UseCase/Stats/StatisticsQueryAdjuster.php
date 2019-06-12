<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Repository\ResourceKindRepository;

class StatisticsQueryAdjuster {
    private const MOMENT_DATE_FORMAT = 'Y-m-d\TH:i:s';

    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceContentsAdjuster $resourceContentsAdjuster, ResourceKindRepository $resourceKindRepository) {
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /** @param StatisticsQuery $command */
    public function adjustCommand(Command $command): Command {
        return new StatisticsQuery(
            $command->getDateFrom(),
            $command->getDateTo(),
            $this->resourceKindIdsToResourceKinds($command->getResourceKinds()),
            $this->resourceContentsAdjuster->adjust($command->getResourceContentsFilter()),
            $command->getResourceId(),
            $command->getEventGroup(),
            $command->getAggregation(),
            $command->isGroupedByResources()
        );
    }

    private function resourceKindIdsToResourceKinds(array $resourceKindIds) {
        return array_map(
            function ($resourceKind) {
                if (!$resourceKind instanceof ResourceKind) {
                    $resourceKind = $this->resourceKindRepository->findByNameOrId($resourceKind);
                }
                return $resourceKind;
            },
            $resourceKindIds
        );
    }
}
