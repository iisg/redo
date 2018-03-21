<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\AuditEntry;

interface AuditEntryRepository {
    /** @return AuditEntry[] */
    public function findAll();
}
