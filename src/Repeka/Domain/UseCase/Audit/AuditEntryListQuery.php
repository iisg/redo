<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class AuditEntryListQuery extends AbstractListQuery implements NonValidatedCommand {
    /** @var array */
    private $commandNames;

    public function __construct(array $commandNames, int $page, int $resultsPerPage) {
        parent::__construct($page, $resultsPerPage);
        $this->commandNames = $commandNames;
    }

    public static function builder(): AuditEntryListQueryBuilder {
        return new AuditEntryListQueryBuilder();
    }

    public function getCommandNames(): array {
        return $this->commandNames;
    }
}
