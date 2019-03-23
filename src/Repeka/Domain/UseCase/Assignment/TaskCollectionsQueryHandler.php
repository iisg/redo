<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Constants\TaskStatus;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Utils\EntityUtils;

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
        $groupedTasks = $this->tasksFinder->getAllTasks($query->getUser());
        $filteredGroups = [];
        foreach ($query->getSingleCollectionQueries() as $resourceClass => $tasksByStatus) {
            $filteredGroups[$resourceClass] = [];
            /** @var ResourceListQuery $singleCollectionQuery */
            foreach ($tasksByStatus as $taskStatus => $singleCollectionQuery) {
                if (!empty($groupedTasks[$resourceClass][$taskStatus])) {
                    $resources = $groupedTasks[$resourceClass][$taskStatus];
                    $ids = EntityUtils::mapToIds($resources);
                    $singleCollectionQuery = $singleCollectionQuery->extend()->filterByIds($ids)->build();
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
            foreach ($groupedTasks as $resourceClass => $tasksByStatus) {
                foreach ($tasksByStatus as $taskStatus => $resources) {
                    if (!ArrayUtils::keyPathExists([$resourceClass, $taskStatus], $filteredGroups)) {
                        $resources = new PageResult($resources, count($resources));
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
