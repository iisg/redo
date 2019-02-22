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
        $this->froms[] = 'audit ae';
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
            $this->wheres[] = 'ae.created_at >= :dateFrom::timestamp at time zone \'utc\'';
            $this->params['dateFrom'] = $this->query->getDateFrom();
        }
        if ($this->query->getDateTo()) {
            $this->wheres[] = 'ae.created_at < :dateTo::timestamp at time zone \'utc\'';
            $this->params['dateTo'] = $this->query->getDateTo();
        }
    }

    private function filterByUsers() {
        if ($this->query->getUsers()) {
            $this->wheres[] = 'ae.user_id IN(:users)';
            $this->params['users'] = $this->query->getUsers();
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
