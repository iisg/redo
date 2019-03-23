<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

class TaskCollectionsQuery extends AbstractCommand implements AdjustableCommand {
    use RequireNoRoles;

    /** @var User */
    private $user;
    /** @var ResourceListQuery[][] */
    private $singleCollectionQueries;
    /** @var bool */
    private $onlyQueriedCollections;

    private function __construct() {
    }

    public static function builder(): TaskCollectionsQueryBuilder {
        return new TaskCollectionsQueryBuilder();
    }

    public static function withParams(
        User $user,
        array $singleCollectionQueries,
        bool $onlyQueriedCollections
    ): TaskCollectionsQuery {
        $query = new self();
        $query->user = $user;
        $query->singleCollectionQueries = $singleCollectionQueries;
        $query->onlyQueriedCollections = $onlyQueriedCollections;
        return $query;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getSingleCollectionQueries(): array {
        return $this->singleCollectionQueries;
    }

    public function onlyQueriedCollections(): bool {
        return $this->onlyQueriedCollections;
    }
}
