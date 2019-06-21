<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;

class AuditEntryListQuerySqlFactory extends ResourceListQuerySqlFactory {

    public function __construct(AuditEntryListQuery $query) {
        $this->query = $query;
        $this->alias = 'ae';
        $this->build();
    }

    private function build() {
        $this->froms[] = 'audit ae LEFT JOIN "user" u ON ae.user_id = u.id';
        $this->filterByCommandNames();
        $this->filterByDate();
        $this->filterByContents(
            $this->query->getResourceContentsFilter(),
            "ae.data->'before'->'resource'->'contents'"
        );
        $this->filterByContents(
            $this->query->getResourceContentsFilter(),
            "ae.data->'after'->'resource'->'contents'"
        );
        $this->filterByUsers();
        $this->filterByAuditResourceKinds();
        $this->filterByTransitions();
        $this->filterByResourceId();
        $this->orderBy[] = 'ae.created_at DESC, id DESC';
        $this->paginate();
    }

    private function filterByCommandNames() {
        if ($this->query->getCommandNames()) {
            $this->wheres[] = 'ae.commandName IN(:commandNames)';
            $this->params['commandNames'] = $this->query->getCommandNames();
        }
    }

    private function filterByDate() {
        if ($this->query->getDateFrom()) {
            $this->wheres[] = 'ae.created_at >= :dateFrom::timestamp';
            $this->params['dateFrom'] = $this->query->getDateFrom()->format(\DateTime::ATOM);
        }
        if ($this->query->getDateTo()) {
            $this->wheres[] = 'ae.created_at < :dateTo::timestamp';
            $this->params['dateTo'] = $this->query->getDateTo()->format(\DateTime::ATOM);
        }
    }

    private function filterByUsers() {
        if ($this->query->getUsers()) {
            $this->wheres[] = 'u.user_data_id IN(:users)';
            $this->params['users'] = $this->query->getUsers();
        }
    }

    private function filterByAuditResourceKinds() {
        if ($this->query->getResourceKinds()) {
            $tempWhereAlternatives = [];
            $tempWhereAlternatives[] = "ae.data->'before'->'resource'->'kindId' IN(:resourceKinds)";
            $tempWhereAlternatives[] = "ae.data->'after'->'resource'->'kindId' IN(:resourceKinds)";
            $tempWhereAlternatives[] = "ae.data->'resource'->'kindId' IN(:resourceKinds)";
            $this->wheres[] = '(' . implode(' OR ', $tempWhereAlternatives) . ')';
            $this->params['resourceKinds'] = $this->query->getResourceKinds();
        }
    }

    private function filterByTransitions() {
        if ($this->query->getTransitions()) {
            $this->wheres[] = "ae.data->'transitionId' IN (:transitions)";
            $this->params['transitions'] = $this->query->getTransitions();
        }
    }

    private function filterByResourceId() {
        if ($this->query->getResourceId() != null) {
            $this->whereAlternatives[] = "ae.data->'before'->'resource'->'id' = :id";
            $this->whereAlternatives[] = "ae.data->'after'->'resource'->'id' = :id";
            $this->whereAlternatives[] = "ae.data->'resource'->'id' = :id";
            $this->whereAlternatives[] = "ae.data->'resourceId' = :id";
            $this->params['id'] = $this->query->getResourceId();
        }
    }
}
