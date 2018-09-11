<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;

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
        $this->filterByName();
        $this->addOrderBy();
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getQuery(): string {
        return $this->getSelectQuery($this->alias . '.*') . sprintf('ORDER BY %s %s', implode(', ', $this->orderBy), $this->limit);
    }

    public function getTotalCountQuery(): string {
        return $this->getSelectQuery('COUNT(id)') . 'GROUP BY id';
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

    private function filterByName(): void {
        if ($this->query->getName()) {
            $this->wheres[] = 'jsonb_contains(rk.label, :nameFilters) = TRUE';
            $this->params['nameFilters'] = json_encode($this->query->getName());
        }
    }

    private function addOrderBy(): void {
        $sortByIds = $this->query->getSortBy();
        foreach ($sortByIds as $columnSort) {
            $sortBy = $columnSort['columnId'];
            $direction = $columnSort['direction'];
            $language = $columnSort['language'];
            if ($sortBy == 'id') {
                $this->orderBy[] = "rk.id " . $direction;
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
