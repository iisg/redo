<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Entity\ResourceContents;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AuditEntryListQueryBuilder extends AbstractListQueryBuilder {
    private $commandNames = [];
    private $dateFrom = "";
    private $dateTo = "";
    private $users = [];
    private $resourceKinds = [];
    private $transitions = [];
    private $resourceContents = [];
    private $resourceId = 0;
    private $regexFilter = '';

    public function __construct() {
        $this->dateFrom = new \DateTime('2000-01-01');
        $this->dateTo = new \DateTime('+1 day');
    }

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
            $this->resourceId,
            $this->regexFilter
        );
    }

    public function filterByCommandNames(array $commandNames): self {
        $this->commandNames = $commandNames;
        return $this;
    }

    public function filterByDateFrom($dateFrom): self {
        $this->dateFrom = $dateFrom instanceof \DateTime ? $dateFrom : new \DateTime($dateFrom);
        return $this;
    }

    public function filterByDateTo($dateTo): self {
        $this->dateTo = $dateTo instanceof \DateTime ? $dateTo : new \DateTime($dateTo);
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

    public function filterByRegex(string $regex): self {
        $this->regexFilter = $regex;
        return $this;
    }
}
