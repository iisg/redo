<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;

class ResourceTreeQuerySqlFactory extends ResourceListQuerySqlFactory {
    private $rootDescendantsFilter = '';
    private $depthRange = '';
    private $siblingsFilter = '';

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(ResourceTreeQuery $query) {
        $this->query = $query;
        $this->alias = 'r';
        $this->build();
    }

    private function build() {
        $this->froms['r'] = 'resource r';
        $this->filterByResourceClasses();
        $this->filterByResourceKinds();
        $this->filterByContents($this->query->getContentsFilter());
        $this->filterByRoot();
        $this->filterByDepth();
        $this->paginateLevels();
    }

    private function filterByRoot() {
        if ($this->query->getRootId() === 0) {
            $this->rootDescendantsFilter = 'WHERE ancestors.next_ancestor_id IS NULL';
        } else {
            $this->rootDescendantsFilter = 'WHERE ancestors.list [1] = :root';
            $this->params['root'] =  $this->query->getRootId();
        }
    }

    private function filterByDepth() {
        $start = $this->query->hasRootId() ? '2' : '1';
        if ($this->query->hasDepth() && is_numeric($this->query->getDepth())) {
            $end = $this->query->hasRootId() ? $this->query->getDepth() + 1 : $this->query->getDepth();
        } else {
            $end = '10000'; // in PostgreSQL 9.6 one of the bounds can be omitted, but not in 9.4
        }
        $this->depthRange = "[$start : $end]";
    }

    private function paginateLevels() {
        $firstLevelFilter = 'node_info.depth = 1';
        if ($this->query->getPage() !== 0) {
            $pageStart = 1 + ($this->query->getPage() - 1) * $this->query->getResultsPerPage();
            $pageEnd = $pageStart + $this->query->getResultsPerPage();
            if ($this->query->oneMoreElements()) {
                $pageEnd += 1;
            }
            $firstLevelFilter .= " AND node_info.row >= :pageStart AND node_info.row < :pageEnd";
            $this->params['pageStart'] = $pageStart;
            $this->params['pageEnd'] = $pageEnd;
        }
        $nextLevelsFilter = ' node_info.depth >= 2';
        if ($this->query->hasSiblings()) {
            $maxSiblings = $this->query->getSiblings();
            if ($this->query->oneMoreElements()) {
                $maxSiblings += 1;
            }
            $nextLevelsFilter .= " AND node_info.row <= :maxSiblings";
            $this->params['maxSiblings'] = $maxSiblings;
        }
        $this->siblingsFilter = " WHERE $firstLevelFilter OR$nextLevelsFilter";
    }

    public function getTreeQuery(): string {
        $selectAllMatches = $this->getSelectQuery(
            'r.id AS matched_id,'
            . 'ARRAY [r.id] :: INT [] AS list,'
            . '((r.contents -> \'-1\' ->> 0) :: JSONB -> \'value\') :: TEXT :: INT AS next_ancestor_id'
        );
        $range = $this->depthRange;
        /*
         * for root resource
         * find all matching resources within subtree below X
         * and return all resources between resource X and matches within some depth
         * but no more than Y siblings below each resource in tree.
         * It does not work fully correctly though:
         * result contains unnecessary tree parts when number of siblings is set.
         * Solution 1: discard them in backend (currently used)
         * Solution 2: recursively join looking for parent up to the queried ancestor, but then query cost EXPLODES in explain
         * Solution 3: do not filter out more than Y siblings and do that in frontend
         */
        return <<<SQL
WITH subtree (tree_node_id, tree_node_parent_id, depth) AS (
  -- descendants_list returns list of all nodes below given root and within depth
  -- row_num and list are only for computing parents of each node
  WITH descendants (tree_node_id, row_num, list) AS (
    -- ancestors returns list of all matched resources, each one with list of all of its ancestors
    WITH RECURSIVE ancestors (matched_id, list, next_ancestor_id) AS (
      -- Find resources like in usual query: SELECT r.id FROM resources r WHERE ...
      $selectAllMatches
      -- Recursively join resources with their children and append resource id to array with ancestors
      UNION ALL
      SELECT
        a.matched_id                                                    AS matched_id,
        r.id || a.list                                                  AS list,
        ((r.contents -> '-1' ->> 0) :: JSONB -> 'value') :: TEXT :: INT AS next_ancestor_id
      FROM resource AS r
        JOIN ancestors AS a
          ON r.id = a.next_ancestor_id
    )
    -- leave only rows pointing to root
    -- and where path is longest (= found all ancestors) (using DISTINCT ON with ORDER BY)
    -- How it works:
    -- 1) WHERE clause filters rows returned from ancestors table, leaving only those below root,
    -- 2) all paths in all rows within depth are selected by unnest (rows are multiplied)
    -- 3) rows are ordered and duplicates are removed.
    SELECT DISTINCT ON (tree_node_id)
      -- when querying for top-level resources (no root_id), first relevant result is in list[1]
      -- but when root_id is given, root is in list[1], first relevant result is in list[2]
      unnest(ancestors.list $range)                 AS tree_node_id,
      generate_subscripts(ancestors.list $range, 1) AS row_num,
      ancestors.list $range                         AS list
    FROM ancestors
    $this->rootDescendantsFilter
    ORDER BY tree_node_id, cardinality(ancestors.list) DESC
  )
  SELECT
    descendants.tree_node_id                   AS tree_node_id,
    descendants.list [descendants.row_num - 1] AS tree_node_parent_id,
    descendants.row_num                        AS depth
  FROM descendants
)
SELECT r.*
FROM (
       -- group resources by parents and assign cardinal numbers within each group
       SELECT
         ROW_NUMBER()
         OVER (
           PARTITION BY subtree.tree_node_parent_id
           ORDER BY subtree.tree_node_id ) AS row,
         subtree.*
       FROM
         subtree
     ) AS node_info
  JOIN resource r
    ON r.id = node_info.tree_node_id
  $this->siblingsFilter
SQL;
    }

    public function getMatchingResourcesQuery(array $idsToCheck): string {
        $query = $this->getSelectQuery($this->alias . '.id AS id');
        $parametrizedIds = [];
        foreach ($idsToCheck as $id) {
            $paramName = "res${id}";
            $this->params[$paramName] = $id;
            $parametrizedIds[] = ":${paramName}";
        }
        if (count($idsToCheck)) {
            $query .= ' AND id IN (' . implode(', ', $parametrizedIds) . ')';
        } else {
            $query .= ' AND 1=0 ';
        }
        return $query;
    }
}
