<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\UseCase\Assignment\TaskCollectionsQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/** @Route("/tasks") */
class TasksController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     * @throws \Assert\AssertionFailedException
     */
    public function getListAction(Request $request) {
        $user = $this->getUser();
        $queries = $request->get('queries', []);
        $onlyQueriedCollections = $request->query->getBoolean('onlyQueriedCollections', false);
        Assertion::isArray($queries);
        Assertion::boolean($onlyQueriedCollections);
        $query = TaskCollectionsQuery::builder()
            ->forUser($user);
        if ($onlyQueriedCollections) {
            $query->onlyQueriedCollections();
        }
        foreach ($queries as $resourceClass => $group) {
            foreach ($group as $taskStatus => $queryParams) {
                $collectionQuery = $this->getResourceListQueryBuilder($queryParams)->build();
                $query->addSingleCollectionQuery($resourceClass, $taskStatus, $collectionQuery);
            }
        }
        $tasks = $this->handleCommand($query->build());
        return $this->createJsonResponse($tasks);
    }

    private function getResourceListQueryBuilder(array $params): ResourceListQueryBuilder {
        $sortByIds = $params['sortByIds'] ?? [];
        $workflowPlacesIds = $params['workflowPlacesIds'] ?? [];
        $contentsFilter = $params['contentsFilter'] ?? [];
        Assertion::isArray($sortByIds);
        Assertion::isArray($workflowPlacesIds);
        $resourceListQueryBuilder = ResourceListQuery::builder()
            ->filterByContents(is_array($contentsFilter) ? $contentsFilter : [])
            ->sortBy($sortByIds)
            ->filterByWorkflowPlacesIds($workflowPlacesIds);
        if (array_key_exists('page', $params) || array_key_exists('resultsPerPage', $params)) {
            $page = $params['page'] ?? 1;
            $resultsPerPage = $params['resultsPerPage'] ?? 10;
            $resourceListQueryBuilder->setPage($page)->setResultsPerPage($resultsPerPage);
        }
        return $resourceListQueryBuilder;
    }
}
