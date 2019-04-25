<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\Utils\StringUtils;

class ResourceKindListQuerySqlFactory {
    /** @var ResourceKindListQuery */
    protected $query;

    protected $froms = [];
    protected $wheres = ['1=1'];
    protected $params = [];
    protected $orderBy = [];
    protected $limit = '';
    protected $alias = 'rk';

    public function __construct(ResourceKindListQuery $query) {
        $this->query = $query;
        $this->build();
    }

    private function build() {
        $this->froms[] = 'resource_kind rk';
        $this->filterByIds();
        $this->filterByResourceClasses();
        $this->filterByMetadataId();
        $this->filterByNames();
        $this->filterByWorkflowId();
        $this->addOrderBy();
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getQuery(): string {
        return $this->getSelectQuery($this->alias . '.*') . sprintf('ORDER BY %s %s', implode(', ', $this->orderBy), $this->limit);
    }

    public function getTotalCountQuery(): string {
        $alias = $this->alias;
        return 'SELECT COUNT(*) FROM (' . $this->getSelectQuery("$alias.id") . "GROUP BY $alias.id) total";
    }

    private function getSelectQuery(string $what) {
        return sprintf(
            'SELECT %s FROM %s WHERE %s ',
            $what,
            implode(', ', $this->froms),
            implode(' AND ', $this->wheres)
        );
    }

    private function filterByIds(): void {
        if (!empty($this->query->getIds())) {
            $this->wheres[] = 'id IN(:ids)';
            $this->params['ids'] = $this->query->getIds();
        }
    }

    private function filterByResourceClasses(): void {
        if (!empty($this->query->getResourceClasses())) {
            $this->wheres[] = 'resource_class IN(:resourceClasses)';
            $this->params['resourceClasses'] = $this->query->getResourceClasses();
        }
    }

    private function filterByMetadataId(): void {
        if ($this->query->getMetadataId()) {
            $this->wheres[] = 'jsonb_contains(rk.metadata_list, :metadataIdFilters) = TRUE';
            $this->params['metadataIdFilters'] = json_encode([['id' => $this->query->getMetadataId()]]);
        }
    }

    private function filterByNames(): void {
        if ($this->query->getNames()) {
            $this->wheres[] = 'rk.name IN (:names)';
            $this->params['names'] = array_map([StringUtils::class, 'normalizeEntityName'], $this->query->getNames());
        }
    }

    private function filterByWorkflowId(): void {
        if ($this->query->getWorkflowId()) {
            $this->wheres[] = 'rk.workflow_id = :workflowId';
            $this->params['workflowId'] = $this->query->getWorkflowId();
        }
    }

    private function addOrderBy(): void {
        $sortByIds = $this->query->getSortBy();
        foreach ($sortByIds as $columnSort) {
            $sortBy = $columnSort['columnId'];
            $direction = $columnSort['direction'];
            $language = $columnSort['language'] ?? 'PL';
            if ($sortBy === 'id' || $sortBy === 'name') {
                $this->orderBy[] = "rk.$sortBy " . $direction;
            } elseif ($sortBy == 'label') {
                $this->orderBy[] = "rk.label->'$language' $direction";
            }
        }
        if (empty($this->orderBy)) {
            $this->orderBy[] = "rk.id DESC";
        }
    }

    protected function paginate(): void {
        if ($this->query->paginate()) {
            $offset = ($this->query->getPage() - 1) * $this->query->getResultsPerPage();
            $this->limit = "LIMIT {$this->query->getResultsPerPage()} OFFSET $offset";
        }
    }
}
