<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\UseCase\Audit\AuditedCommandNamesQuery;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Domain\UseCase\PageResult;

interface AuditEntryRepository {
    /** @return AuditEntry[] */
    public function findAll();

    /** @return PageResult|AuditEntry[] */
    public function findByQuery(AuditEntryListQuery $query): PageResult;

    /** @return string[] */
    public function getAuditedCommandNames(AuditedCommandNamesQuery $query): array;
}
