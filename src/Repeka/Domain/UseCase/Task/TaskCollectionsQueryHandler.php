<?php
namespace Repeka\Domain\UseCase\Task;

use Repeka\Domain\Constants\TaskStatus;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\ArrayUtils;

class TaskCollectionsQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var TasksFinder */
    private $tasksFinder;

    public function __construct(ResourceRepository $resourceRepository, TasksFinder $tasksFinder) {
        $this->resourceRepository = $resourceRepository;
        $this->tasksFinder = $tasksFinder;
    }

    public function handle(TaskCollectionsQuery $query) {
        $groupedTasksIds = $this->tasksFinder->getAllTasks($query->getUser());
        $filteredGroups = [];
        foreach ($query->getSingleCollectionQueries() as $resourceClass => $tasksByStatus) {
            $filteredGroups[$resourceClass] = [];
            /** @var ResourceListQuery $singleCollectionQuery */
            foreach ($tasksByStatus as $taskStatus => $singleCollectionQuery) {
                if (!empty($groupedTasksIds[$resourceClass][$taskStatus])) {
                    $resourceIds = $groupedTasksIds[$resourceClass][$taskStatus];
                    $singleCollectionQuery = $singleCollectionQuery->extend()->filterByIds($resourceIds)->build();
                    $resources = $this->resourceRepository->findByQuery($singleCollectionQuery);
                    $filteredGroups[$resourceClass][$taskStatus] = new TaskCollection(
                        $resourceClass,
                        new TaskStatus($taskStatus),
                        $resources
                    );
                }
            }
        }
        if (!$query->onlyQueriedCollections()) {
            foreach ($groupedTasksIds as $resourceClass => $tasksByStatus) {
                foreach ($tasksByStatus as $taskStatus => $resourceIds) {
                    if ($resourceIds && !ArrayUtils::keyPathExists([$resourceClass, $taskStatus], $filteredGroups)) {
                        $query = ResourceListQuery::builder()->filterByIds($resourceIds)->build();
                        $resources = $this->resourceRepository->findByQuery($query);
                        $filteredGroups[$resourceClass][$taskStatus] = new TaskCollection(
                            $resourceClass,
                            new TaskStatus($taskStatus),
                            $resources
                        );
                    }
                }
            }
        }
        return ArrayUtils::flatten($filteredGroups);
    }
}
