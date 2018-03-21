<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Repository\AuditEntryRepository;

class AuditedCommandNamesQueryHandler {
    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    public function __construct(AuditEntryRepository $auditEntryRepository) {
        $this->auditEntryRepository = $auditEntryRepository;
    }

    public function handle(): array {
        return $this->auditEntryRepository->getAuditedCommandNames();
    }
}
