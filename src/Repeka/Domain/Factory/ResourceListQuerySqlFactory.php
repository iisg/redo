<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;

class ResourceListQuerySqlFactory {
    /** @var ResourceListQuery */
    protected $query;

    protected $froms = [];
    protected $wheres = ['1=1'];
    protected $whereAlternatives = [];
    protected $params = [];
    protected $orderBy = [];
    protected $limit = '';
    protected $alias = 'r';

    public function __construct(ResourceListQuery $query) {
        $this->query = $query;
        $this->build();
    }

    private function build() {
        $this->froms['r'] = 'resource r';
        $this->filterByIds();
        $this->filterByResourceClasses();
        $this->filterByResourceKinds();
        $this->filterByParentId();
        $this->filterByWorkflowPlacesIds();
        $this->filterByTopLevel();
        foreach ($this->query->getContentsFilters() as $filter) {
            $this->filterByContents($filter);
        }
        $this->addOrderBy();
        $this->paginate();
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getPageQuery(): string {
        return $this->getSelectQuery($this->alias . '.*')
            . sprintf('ORDER BY %s %s', implode(', ', $this->orderBy), $this->limit);
    }

    public function getTotalCountQuery(): string {
        // GROUP BY id clause is needed because of use of cross join (multiple FROM tables).
        // Each resource might return multiple rows, so simple SELECT COUNT(*) is not correct.
        $alias = $this->alias;
        return 'SELECT COUNT(*) FROM (' . $this->getSelectQuery("$alias.id") . " GROUP BY $alias.id) total";
    }

    /**
     * WHERE clause in the database query has a form of:
     * WHERE     $this->wheres[0]
     *       AND ...
     *       AND $this->wheres[end]
     *       AND ($this->whereAlternatives[0] OR ... OR $this->whereAlternatives[end]).
     */
    protected function getSelectQuery(string $what) {
        $wheres = $this->wheres;
        if (!empty($this->whereAlternatives)) {
            $wheres[] = '(' . implode(' OR ', $this->whereAlternatives) . ')';
        }
        return sprintf(
            'SELECT %s FROM %s WHERE %s ',
            $what,
            implode(', ', $this->froms),
            implode(' AND ', $wheres)
        );
    }

    private function filterByIds(): void {
        if ($this->query->getIds()) {
            $this->wheres[] = 'id IN(:ids)';
            $this->params['ids'] = $this->query->getIds();
        }
    }

    protected function filterByResourceClasses(): void {
        if ($this->query->getResourceClasses()) {
            $this->wheres[] = 'resource_class IN(:resourceClasses)';
            $this->params['resourceClasses'] = $this->query->getResourceClasses();
        }
    }

    protected function filterByResourceKinds(): void {
        if ($this->query->getResourceKinds()) {
            $this->wheres[] = 'kind_id IN(:resourceKindIds)';
            $this->params['resourceKindIds'] = EntityUtils::mapToIds($this->query->getResourceKinds());
        }
    }

    private function filterByParentId(): void {
        if ($this->query->getParentId()) {
            $parentMetadata = SystemMetadata::PARENT;
            $this->wheres[] = "r.contents->'$parentMetadata' @> :parentSearchValue";
            $this->params['parentSearchValue'] = json_encode([['value' => $this->query->getParentId()]]);
        }
    }

    private function filterByWorkflowPlacesIds(): void {
        if ($this->query->getWorkflowPlacesIds()) {
            $this->froms['place'] = 'JSONB_OBJECT_KEYS(r.marking) place';
            $this->wheres[] = "place IN(:workflowPlacesIds)";
            $this->params['workflowPlacesIds'] = $this->query->getWorkflowPlacesIds();
        }
    }

    private function filterByTopLevel(): void {
        if ($this->query->onlyTopLevel()) {
            $parentMetadata = SystemMetadata::PARENT;
            $this->wheres[] = "(r.contents->'$parentMetadata' = '[]' OR JSONB_EXISTS(r.contents, '$parentMetadata') = FALSE)";
        }
    }

    /**
     * Each call to filterByContents adds an alternative to search query.
     */
    protected function filterByContents(
        ResourceContents $resourceContents,
        $glueBetweenSameMetadataFilters = ' AND ',
        $contentsPath = 'r.contents'
    ): void {
        $nextFilterId = $this->getUnusedParamId();
        $contentWhere = [];
        $resourceContents->forEachValue(
            function (MetadataValue $value, int $metadataId) use ($contentsPath, &$contentWhere, &$nextFilterId, &$metadataInFrom) {
                $this->froms["m$nextFilterId"] = $this->jsonbArrayElements("$contentsPath->'$metadataId'") . " m$nextFilterId";
                $paramName = "mFilter$nextFilterId";
                if (is_int($value->getValue())) {
                    $whereClause = "m$nextFilterId->>'value' = :$paramName";
                } else {
                    $whereClause = "m$nextFilterId->>'value' ~* :$paramName";
                }
                $this->params[$paramName] = $value->getValue();
                $contentWhere[$metadataId][] = $whereClause;
                $nextFilterId++;
            }
        );
        if ($contentWhere) {
            $gluedClauses = array_map(
                function (array $whereClauses) use ($glueBetweenSameMetadataFilters) {
                    return implode($glueBetweenSameMetadataFilters, $whereClauses);
                },
                $contentWhere
            );
            $joinedClause = '(' . implode(') AND (', $gluedClauses) . ')';
            $this->whereAlternatives[] = $joinedClause;
        }
    }

    protected function addOrderBy(): void {
        $sortByIds = $this->query->getSortBy();
        foreach ($sortByIds as $columnSort) {
            $sortId = $columnSort['columnId'];
            $direction = $columnSort['direction'];
            if ($sortId == 'id') {
                $this->orderBy[] = "r.id " . $direction;
            } elseif ($sortId == 'kindId') {
                $this->orderBy[] = "r.kind_id " . $direction;
            } else {
                $this->orderBy[] = $this->jsonbArrayElements("(r.contents->'$sortId')") . "->>'value' $direction";
            }
        }
        if (empty($this->orderBy)) {
            $this->orderBy[] = "r.id DESC";
        }
    }

    protected function paginate(): void {
        if ($this->query->paginate()) {
            $offset = ($this->query->getPage() - 1) * $this->query->getResultsPerPage();
            $this->limit = "LIMIT {$this->query->getResultsPerPage()} OFFSET $offset";
        }
    }

    protected function getUnusedParamId(): int {
        return count($this->params);
    }

    private function jsonbArrayElements($arg) {
        return "jsonb_array_elements(COALESCE($arg, '[{}]'))";
    }
}
