<?php
namespace Repeka\Domain\UseCase\Stats;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceContents;

class StatisticsQueryBuilder {
    private $dateFrom = '';
    private $dateTo = '';
    private $resourceKinds = [];
    private $resourceContents = [];
    private $resourceId = 0;
    private $aggregation = 'millennium';
    private $groupedByResources = false;
    private $eventGroup = '';

    public function __construct() {
        $this->dateFrom = new \DateTime('2000-01-01');
        $this->dateTo = new \DateTime('+1 day');
    }

    public function build(): StatisticsQuery {
        return new StatisticsQuery(
            $this->dateFrom,
            $this->dateTo,
            $this->resourceKinds,
            $this->resourceContents,
            $this->resourceId,
            $this->eventGroup,
            $this->aggregation,
            $this->groupedByResources
        );
    }

    public function filterByDateFrom($dateFrom): self {
        $this->dateFrom = $dateFrom instanceof \DateTime ? $dateFrom : new \DateTime($dateFrom);
        return $this;
    }

    public function filterByDateTo($dateTo): self {
        $this->dateTo = $dateTo instanceof \DateTime ? $dateTo : new \DateTime($dateTo);
        return $this;
    }

    public function filterByResourceKinds(array $resourceKinds): self {
        $this->resourceKinds = $resourceKinds;
        return $this;
    }

    /** @param ResourceContents|array $resourceContents */
    public function filterByResourceContents($resourceContents): self {
        $this->resourceContents = $resourceContents;
        return $this;
    }

    public function filterByResourceId(int $resourceId): self {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function aggregateBy(string $aggregation): self {
        Assertion::inArray($aggregation, ['millennium', 'year', 'month', 'week', 'day', 'hour']);
        $this->aggregation = $aggregation;
        return $this;
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function groupByResources($groupedByResources = true): self {
        $this->groupedByResources = $groupedByResources;
        return $this;
    }

    public function filterByEventGroup(string $eventGroup): self {
        $this->eventGroup = $eventGroup;
        return $this;
    }
}
