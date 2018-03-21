<?php
namespace Repeka\Domain\UseCase\Audit;

class AuditEntryListQueryBuilder extends AbstractListQueryBuilder {
    private $commandNames = [];

    public function build(): AuditEntryListQuery {
        return new AuditEntryListQuery($this->commandNames, $this->page, $this->resultsPerPage);
    }

    public function filterByCommandNames(array $commandNames): AuditEntryListQueryBuilder {
        $this->commandNames = $commandNames;
        return $this;
    }
}
