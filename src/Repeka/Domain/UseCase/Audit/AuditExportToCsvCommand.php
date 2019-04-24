<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class AuditExportToCsvCommand extends AbstractCommand implements NonValidatedCommand {
    /** @var AuditEntryListQueryBuilder */
    private $auditEntryListQueryBuilder;
    /** @var string[] */
    private $customColumns;

    public function __construct(AuditEntryListQueryBuilder $auditEntryListQueryBuilder, array $customColumns) {
        $this->auditEntryListQueryBuilder = $auditEntryListQueryBuilder;
        $this->customColumns = $customColumns;
    }

    public function getAuditEntryListQueryBuilder(): AuditEntryListQueryBuilder {
        return $this->auditEntryListQueryBuilder;
    }

    /** @return string[] */
    public function getCustomColumns(): array {
        return $this->customColumns;
    }
}
