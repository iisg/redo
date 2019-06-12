<?php
namespace Repeka\Domain\Factory;

use Doctrine\ORM\Query\ResultSetMapping;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Stats\StatisticsQuery;

class StatisticsQuerySqlFactory {

    /** @var StatisticsQuery */
    protected $query;

    protected $cols = [
        'event_name "eventName"',
        'MIN(event_group) "eventGroup"',
        'COUNT(*) count',
        'date_trunc(:agg, event_date_time) bucket',
    ];
    protected $froms = [];
    protected $wheres = [];
    protected $params = [];
    protected $orderBy = [];
    protected $groupBy = ['event_name', 'bucket'];

    public function __construct(StatisticsQuery $query) {
        $this->query = $query;
        $this->build();
    }

    private function build() {
        $this->froms['r'] = 'event_log e LEFT JOIN resource r ON e.resource_id = r.id';
        $this->filterByDates();
        $this->filterByEventGroup();
        $this->filterByResourceParams();
        $this->aggregate();
        if ($this->query->isGroupedByResources()) {
            $this->groupByResources();
        }
    }

    private function filterByDates() {
        $this->wheres[] = 'event_date_time BETWEEN :fromDate::timestamp AND :toDate::timestamp';
        $this->params['fromDate'] = $this->query->getDateFrom()->format(\DateTime::ATOM);
        $this->params['toDate'] = $this->query->getDateTo()->format(\DateTime::ATOM);
    }

    private function filterByEventGroup() {
        if ($this->query->getEventGroup()) {
            $this->wheres[] = 'event_group = :eventGroup';
            $this->params['eventGroup'] = $this->query->getEventGroup();
        }
    }

    private function filterByResourceParams() {
        $resourceQuery = ResourceListQuery::builder()
            ->filterByIds(array_filter([$this->query->getResourceId()]))
            ->filterByResourceKinds($this->query->getResourceKinds())
            ->filterByContents($this->query->getResourceContentsFilter())
            ->build();
        $resourceListQuerySql = new ResourceListQuerySqlFactory($resourceQuery);
        $resourceWhere = $resourceListQuerySql->getWhereClause();
        if ($resourceWhere) {
            $this->froms = array_merge($resourceListQuerySql->getFroms(), $this->froms);
            $this->wheres[] = $resourceWhere;
            $this->params = array_merge($resourceListQuerySql->getParams(), $this->params);
        }
    }

    private function groupByResources() {
        $this->cols[] = 'resource_id "resourceId"';
        $this->cols[] = 'r.contents->\'-5\'->0->>\'value\' "resourceLabel"';
        $this->groupBy[] = '"resourceId"';
        $this->groupBy[] = '"resourceLabel"';
    }

    private function aggregate() {
        $this->params['agg'] = $this->query->getAggregation();
    }

    public function getParams(): array {
        return $this->params;
    }

    public function getSelectQuery() {
        return sprintf(
            'SELECT %s FROM %s %s GROUP BY %s ORDER BY bucket ASC, "eventGroup" ASC, "eventName" ASC',
            implode(', ', $this->cols),
            implode(', ', $this->froms),
            $this->wheres ? 'WHERE ' . implode(' AND ', $this->wheres) : '',
            implode(', ', $this->groupBy)
        );
    }

    public function getResultSetMapping(): ResultSetMapping {
        return ResultSetMappings::scalar('eventName', 'string')
            ->addScalarResult('eventGroup', 'eventGroup', 'string')
            ->addScalarResult('count', 'count', 'integer')
            ->addScalarResult('bucket', 'bucket', 'datetimetz_immutable')
            ->addScalarResult('resourceId', 'resourceId', 'integer')
            ->addScalarResult('resourceLabel', 'resourceLabel', 'string');
    }
}
