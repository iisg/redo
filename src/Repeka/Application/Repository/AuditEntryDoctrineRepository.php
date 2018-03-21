<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Repository\AuditEntryRepository;

class AuditEntryDoctrineRepository extends EntityRepository implements AuditEntryRepository {
}
