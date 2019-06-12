<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Entity\ResourceContents;

class AuditEntryListQueryBuilder extends AbstractListQueryBuilder {
    private $commandNames = [];
    private $dateFrom = "";
    private $dateTo = "";
    private $users = [];
    private $resourceKinds = [];
    private $transitions = [];
    private $resourceContents = [];
    private $resourceId = 0;

    public function build(): AuditEntryListQuery {
        return new AuditEntryListQuery(
            $this->commandNames,
            $this->dateFrom,
            $this->dateTo,
            $this->users,
            $this->resourceKinds,
            $this->transitions,
            $this->resourceContents instanceof ResourceContents
                ? $this->resourceContents
                : ResourceContents::fromArray($this->resourceContents),
            $this->page,
            $this->resultsPerPage,
            $this->resourceId
        );
    }

    public function filterByCommandNames(array $commandNames): self {
        $this->commandNames = $commandNames;
        return $this;
    }

    public function filterByDateFrom($dateFrom): self {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    public function filterByDateTo($dateTo): self {
        $this->dateTo = $dateTo;
        return $this;
    }

    public function filterByUsers(array $users): self {
        $this->users = $users;
        return $this;
    }

    public function filterByResourceKinds(array $resourceKinds): self {
        $this->resourceKinds = $resourceKinds;
        return $this;
    }

    public function filterByTransitions(array $transitions): self {
        $this->transitions = $transitions;
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
}
