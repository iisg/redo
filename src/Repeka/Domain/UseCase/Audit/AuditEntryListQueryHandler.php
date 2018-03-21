<?php
namespace Repeka\Domain\UseCase\Audit;

use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\UseCase\PageResult;

class AuditEntryListQueryHandler {
    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    public function __construct(AuditEntryRepository $auditEntryRepository) {
        $this->auditEntryRepository = $auditEntryRepository;
    }

    public function handle(AuditEntryListQuery $query): PageResult {
        return $this->auditEntryRepository->findByQuery($query);
    }
}
