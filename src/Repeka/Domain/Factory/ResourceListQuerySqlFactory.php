<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;

class ResourceListQuerySqlFactory {
    /** @var ResourceListQuery */
    protected $query;

    protected $froms = [];
    protected $wheres = ['1=1'];
    protected $params = [];
    protected $orderBy = [];
    protected $limit = '';
    protected $alias = 'r';

    public function __construct(ResourceListQuery $query) {
        $this->query = $query;
        $this->build();
    }

    private function build() {
        $this->froms[] = 'resource r';
        $this->filterByIds();
        $this->filterByResourceClasses();
        $this->filterByResourceKinds();
        $this->filterByParentId();
        $this->filterByWorkflowPlacesIds();
        $this->filterByTopLevel();
        $this->filterByContents($this->query->getContentsFilter());
        $this->addOrderBy();
        $this->paginate();
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getPageQuery(): string {
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
        if ($this->query->getIds()) {
            $this->wheres[] = 'id IN(:ids)';
            $this->params['ids'] = $this->query->getIds();
        }
    }

    private function filterByResourceClasses(): void {
        if ($this->query->getResourceClasses()) {
            $this->wheres[] = 'resource_class IN(:resourceClasses)';
            $this->params['resourceClasses'] = $this->query->getResourceClasses();
        }
    }

    private function filterByResourceKinds(): void {
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
            $this->froms[] = 'JSONB_OBJECT_KEYS(r.marking) place';
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

    protected function filterByContents(ResourceContents $resourceContents, $contentsPath = 'r.contents'): void {
        $resourceContents->forEachValue(
            function ($value, int $metadataId) use ($contentsPath) {
                $escapedMetadataId = str_replace('-', '_', strval($metadataId)); // prevents names like m-2
                $paramName = "mFilter$escapedMetadataId";
                $this->froms[] = "jsonb_array_elements($contentsPath->'$metadataId') m$escapedMetadataId";
                if (is_int($value)) {
                    $this->wheres[] = "m$escapedMetadataId->>'value' = :$paramName";
                    $this->params[$paramName] = $value;
                } else {
                    $this->wheres[] = "m$escapedMetadataId->>'value' ILIKE :$paramName";
                    $this->params[$paramName] = '%' . $value . '%';
                }
            }
        );
    }

    private function addOrderBy(): void {
        $sortByIds = $this->query->getSortBy();
        $sortById = $sortByKindId = null;
        foreach ($sortByIds as $columnSort) {
            $sortId = $columnSort['columnId'];
            $direction = $columnSort['direction'];
            if ($sortId == 'id') {
                $sortById = "r.id " . $direction;
            } elseif ($sortId == 'kindId') {
                $sortByKindId = "r.kind_id " . $direction;
            } else {
                $this->orderBy[] = "jsonb_array_elements(r.contents->'$sortId')->>'value' $direction";
            }
        }
        if ($sortByKindId) {
            $this->orderBy[] = $sortByKindId;
        }
        $this->orderBy[] = $sortById ? $sortById : "r.id DESC";
    }

    protected function paginate(): void {
        if ($this->query->paginate()) {
            $offset = ($this->query->getPage() - 1) * $this->query->getResultsPerPage();
            $this->limit = "LIMIT {$this->query->getResultsPerPage()} OFFSET $offset";
        }
    }
}
