<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\Utils\StringUtils;

class MetadataListQuerySqlFactory {
    /** @var MetadataListQuery */
    protected $query;
    protected $from = 'metadata m';
    protected $wheres = ['1=1'];
    protected $orWheres = [];
    protected $params = [];
    protected $orderBy = [];
    protected $limit = '';
    protected $alias = 'm';

    public function __construct(MetadataListQuery $query) {
        $this->query = $query;
        $this->build();
    }

    private function build() {
        $this->excludeIds();
        $this->filterByIds();
        $this->filterByNames();
        $this->filterByControls();
        $this->filterByResourceClasses();
        $this->filterByTopLevel();
        $this->filterByParentMetadata();
        foreach ($this->query->getRequiredKindIds() as $resourceKindId) {
            $this->filterByRequiredResourceKind($resourceKindId);
        }
        $this->addOrderBy();
        $this->addSystemMetadataIds();
    }

    private function excludeIds(): void {
        if ($this->query->getExcludedIds()) {
            $this->wheres[] = 'id NOT IN(:excludedIds)';
            $this->params['excludedIds'] = $this->query->getExcludedIds();
        }
    }

    private function filterByIds(): void {
        if ($this->query->getIds()) {
            $this->wheres[] = 'id IN(:ids)';
            $this->params['ids'] = $this->query->getIds();
        }
    }

    private function filterByControls() {
        if ($this->query->getControls()) {
            $this->wheres[] = "control IN(:controlNames)";
            $controlNames = array_map(
                function (MetadataControl $control) {
                    return $control->getValue();
                },
                $this->query->getControls()
            );
            $this->params['controlNames'] = $controlNames;
        }
    }

    private function filterByResourceClasses(): void {
        if ($this->query->getResourceClasses()) {
            $this->wheres[] = 'resource_class IN(:resourceClasses)';
            $this->params['resourceClasses'] = $this->query->getResourceClasses();
        }
    }

    private function filterByTopLevel(): void {
        if ($this->query->onlyTopLevel()) {
            $this->wheres[] = "parent_id is null";
        }
    }

    private function filterByParentMetadata(): void {
        if ($this->query->getParent()) {
            $this->wheres[] = "parent_id = :parentId";
            $this->params['parentId'] = $this->query->getParent()->getId();
        }
    }

    private function addSystemMetadataIds(): void {
        if ($this->query->getSystemMetadataIds()) {
            $this->orWheres[] = "id IN(:systemMetadataIds)";
            $this->params['systemMetadataIds'] = $this->query->getSystemMetadataIds();
        }
    }

    private function filterByRequiredResourceKind(int $requiredKindId): void {
        if ($this->query->getRequiredKindIds()) {
            $this->wheres[] = "((constraints->>'resourceKind') is NULL OR (constraints->'resourceKind') @> :requiredKindId)";
            $this->params['requiredKindId'] = $requiredKindId;
        }
    }

    private function filterByNames(): void {
        if ($this->query->getNames()) {
            $this->wheres[] = "name IN(:metadataNames)";
            $this->params['metadataNames'] = array_map([StringUtils::class, 'normalizeEntityName'], $this->query->getNames());
        }
    }

    protected function addOrderBy(): void {
        $this->orderBy[] = "ordinal_number ASC";
        $this->orderBy[] = "id ASC";
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getPageQuery(): string {
        return $this->getSelectQuery($this->alias . '.*')
            . sprintf('ORDER BY %s %s', implode(', ', $this->orderBy), $this->limit);
    }

    /**
     * WHERE clause in the database query has a form of:
     * WHERE (    $this->wheres[0]
     *       AND ...
     *       AND $this->wheres[end] )
     *       OR ($this->orWheres[0])
     *       OR ...
     *       OR ($this->orWheres[end])
     */
    protected function getSelectQuery(string $what) {
        $wheres = $this->orWheres;
        if (!empty($this->wheres)) {
            $wheres[] = '(' . implode(' AND ', $this->wheres) . ')';
        }
        return sprintf(
            'SELECT %s FROM %s WHERE %s ',
            $what,
            $this->from,
            implode(' OR ', $wheres)
        );
    }

    public function getTotalCountQuery(): string {
        return $this->getSelectQuery('COUNT(id)');
    }
}
