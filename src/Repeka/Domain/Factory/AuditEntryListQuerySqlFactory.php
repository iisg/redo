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
        $this->filterByContents($this->query->getResourceContentsFilter(), "ae.data->'resource'->'contents'");
        $this->filterByResourceId();
        $this->orderBy[] = 'ae.created_at DESC';
        $this->paginate();
    }

    private function filterByCommandNames() {
        if ($this->query->getCommandNames()) {
            $this->wheres[] = 'ae.commandName IN(:commandNames)';
            $this->params['commandNames'] = $this->query->getCommandNames();
        }
    }

    private function filterByResourceId() {
        if ($this->query->getResourceId() != null) {
            $this->wheres[] = "ae.data->'resource'->'id' = :id";
            $this->params['id'] = $this->query->getResourceId();
        }
    }
}
