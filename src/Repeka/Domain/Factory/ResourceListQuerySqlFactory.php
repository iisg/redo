<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;

class ResourceListQuerySqlFactory {

    /** @var ResourceListQuery */
    protected $query;

    protected $froms = [];
    private $fromsMetadataMap = [];
    protected $wheres = [];
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
        $this->filterByPermissionMetadata();
        $this->addOrderBy();
        $this->paginate();
    }

    public function getFroms(): array {
        return $this->froms;
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getPageQuery(): string {
        $q = $this->getSelectQuery($this->alias . '.*')
            . sprintf('ORDER BY %s %s', implode(', ', $this->orderBy), $this->limit);
        return $q;
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
        $where = $this->getWhereClause();
        return sprintf(
            'SELECT %s FROM %s %s ',
            $what,
            implode(', ', $this->froms),
            $where ? 'WHERE ' . $where : ''
        );
    }

    public function getWhereClause(): string {
        $wheres = $this->wheres;
        if (!empty($this->whereAlternatives)) {
            $wheres[] = '(' . implode(' OR ', $this->whereAlternatives) . ')';
        }
        return implode(' AND ', $wheres);
    }

    private function filterByIds(): void {
        if ($this->query->getIds()) {
            $this->wheres[] = 'r.id IN(:ids)';
            $this->params['ids'] = $this->query->getIds();
        }
    }

    protected function filterByResourceClasses(): void {
        if ($this->query->getResourceClasses()) {
            $this->wheres[] = 'r.resource_class IN(:resourceClasses)';
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

    private function filterByPermissionMetadata(): void {
        $executor = $this->query->getExecutor();
        if ($executor) {
            $permissionMetadataId = $this->query->getPermissionMetadataId();
            $jsonQuery = $this->jsonbArrayElements("r.contents->'$permissionMetadataId'");
            $visibilityQuery = "EXISTS (SELECT FROM $jsonQuery WHERE value->>'value' IN(:allowedViewers))";
            $this->params['allowedViewers'] = $executor->getGroupIdsWithUserId();
            $resourceClasses = $executor->resourceClassesInWhichUserHasRole(SystemRole::ADMIN());
            if (!empty($resourceClasses)) {
                $visibilityQuery .= ' OR resource_class IN(:userAdminClasses)';
                $this->params['userAdminClasses'] = $resourceClasses;
            }
            $this->wheres[] = '(' . $visibilityQuery . ')';
        }
    }

    /**
     * Each call to filterByContents adds an alternative to search query.
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     */
    protected function filterByContents(ResourceContents $resourceContents, $contentsPath = 'r.contents'): void {
        $nextFilterId = $this->getUnusedParamId();
        $contentWhere = [];
        $resourceContents->forEachValue(
            function (MetadataValue $value, int $metadataId) use ($contentsPath, &$contentWhere, &$nextFilterId, &$metadataInFrom) {
                $filterValues = $value->getValue();
                if (!is_array($filterValues)) {
                    $filterValues = [$filterValues];
                }
                $whereClauses = [];
                foreach ($filterValues as $filterValue) {
                    $paramName = 'filter' . $nextFilterId;
                    if (is_int($filterValue)) {
                        $filterValue = json_encode([['value' => $filterValue]]);
                        $whereClauses[] = "$contentsPath->'$metadataId' @> :$paramName";
                    } elseif ($filterValue == '.+') {
                        $whereClauses[] = "$contentsPath->>'$metadataId' IS NOT NULL";
                        $paramName = null;
                    } else {
                        $metadataFrom = $this->fromsMetadataMap[$contentsPath . $metadataId] ?? null;
                        if (!$metadataFrom) {
                            $metadataFrom = "m$nextFilterId";
                            $this->froms[$metadataFrom] = $this->jsonbArrayElements("$contentsPath->'$metadataId'") . " m$nextFilterId";
                            $this->fromsMetadataMap[$contentsPath . $metadataId] = $metadataFrom;
                        }
                        $condition = '~*';
                        if (preg_match('#^([<>]=?)([\s\d-:T\+]+)$#', $filterValue, $matches)) {
                            list(, $conditionValue, $compareValue) = array_map('trim', $matches);
                            if (!is_numeric($compareValue)) {
                                $timestamp = strtotime($compareValue);
                                if ($timestamp) {
                                    $compareValue = (new \DateTime('@' . $timestamp))->format(\DateTime::ATOM);
                                }
                            }
                            if ($compareValue !== false) {
                                $filterValue = $compareValue;
                                $condition = $conditionValue;
                            }
                        }
                        $whereClauses[] = "$metadataFrom->>'value' $condition :$paramName";
                    }
                    if ($paramName) {
                        $this->params[$paramName] = $filterValue;
                    }
                    $nextFilterId++;
                }
                $contentWhere[$metadataId][] = '(' . implode(') AND (', $whereClauses) . ')';
            }
        );
        if ($contentWhere) {
            $alternatives = array_map(
                function (array $whereClauses) {
                    return implode(' OR ', $whereClauses);
                },
                $contentWhere
            );
            $alternative = '(' . implode(') AND (', $alternatives) . ')';
            $this->whereAlternatives[] = $alternative;
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
                $this->orderBy[] = "r.contents->'$sortId'->0->'value' $direction";
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

    protected function jsonbArrayElements($arg) {
        return "jsonb_array_elements(COALESCE($arg, '[{}]'))";
    }
}
