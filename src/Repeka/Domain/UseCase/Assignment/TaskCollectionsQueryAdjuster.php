<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\UseCase\Resource\ResourceListQueryAdjuster;

class TaskCollectionsQueryAdjuster implements CommandAdjuster {

    /** @var ResourceListQueryAdjuster */
    private $resourceListQueryAdjuster;

    public function __construct(ResourceListQueryAdjuster $resourceListQueryAdjuster) {
        $this->resourceListQueryAdjuster = $resourceListQueryAdjuster;
    }

    /**
     * @param TaskCollectionsQuery $query
     * @return TaskCollectionsQuery
     */
    public function adjustCommand(Command $query): Command {
        return TaskCollectionsQuery::withParams(
            $query->getUser(),
            $this->adjustSingleCollectionQueries($query->getSingleCollectionQueries()),
            $query->onlyQueriedCollections()
        );
    }

    private function adjustSingleCollectionQueries(array $singleCollectionQueries) {
        foreach ($singleCollectionQueries as $resourceClass => $queries) {
            foreach ($queries as $taskStatus => $query) {
                $singleCollectionQueries[$resourceClass][$taskStatus] = $this->resourceListQueryAdjuster->adjustCommand($query);
            }
        }
        return $singleCollectionQueries;
    }
}
