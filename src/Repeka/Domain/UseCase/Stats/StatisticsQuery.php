<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;

class StatisticsQuery extends AbstractCommand implements NonValidatedCommand, AdjustableCommand {
    private $dateFrom;
    private $dateTo;
    private $resourceKinds;
    /** @var ResourceContents */
    private $resourceContentsFilter;
    private $resourceId;
    private $aggregation;
    /** @var bool */
    private $groupedByResources;
    /** @var string */
    private $eventGroup;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(
        \DateTime $dateFrom,
        \DateTime $dateTo,
        array $resourceKinds,
        $resourceContentsFilter,
        int $resourceId,
        string $eventGroup,
        string $aggregation,
        bool $groupedByResources
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->resourceKinds = $resourceKinds;
        $this->resourceContentsFilter = $resourceContentsFilter;
        $this->resourceId = $resourceId;
        $this->eventGroup = $eventGroup;
        $this->aggregation = $aggregation;
        $this->groupedByResources = $groupedByResources;
    }

    public function getDateFrom(): \DateTime {
        return $this->dateFrom;
    }

    public function getDateTo(): \DateTime {
        return $this->dateTo;
    }

    /** @return ResourceKind[] */
    public function getResourceKinds(): array {
        return $this->resourceKinds;
    }

    /** @return ?ResourceContents */
    public function getResourceContentsFilter() {
        return $this->resourceContentsFilter;
    }

    public function getResourceId(): int {
        return $this->resourceId;
    }

    public function getEventGroup(): string {
        return $this->eventGroup;
    }

    public function getAggregation(): string {
        return $this->aggregation;
    }

    public function isGroupedByResources(): bool {
        return $this->groupedByResources;
    }

    public static function builder(): StatisticsQueryBuilder {
        return new StatisticsQueryBuilder();
    }
}
