<?php
namespace Repeka\Domain\UseCase\Task;

use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryBuilder;

class TaskCollectionsQueryBuilder {
    private $user;
    private $singleCollectionQueries = [];
    private $onlyQueriedCollections = false;

    public function forUser(User $user): self {
        $this->user = $user;
        return $this;
    }

    public function singleCollectionQueryBuilder(): ResourceListQueryBuilder {
        return ResourceListQuery::builder();
    }

    public function addSingleCollectionQuery(string $resourceClass, string $collection, ResourceListQuery $query): self {
        if (!array_key_exists($resourceClass, $this->singleCollectionQueries)) {
            $this->singleCollectionQueries[$resourceClass] = [];
        }
        $this->singleCollectionQueries[$resourceClass][$collection] = $query;
        return $this;
    }

    public function onlyQueriedCollections(): self {
        $this->onlyQueriedCollections = true;
        return $this;
    }

    public function build(): TaskCollectionsQuery {
        return TaskCollectionsQuery::withParams($this->user, $this->singleCollectionQueries, $this->onlyQueriedCollections);
    }
}
