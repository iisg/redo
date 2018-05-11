<?php
namespace Repeka\Application\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;

class ResultSetMappings {
    public static function resourceEntity(EntityManagerInterface $em, string $alias = 'w'): ResultSetMapping {
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(ResourceEntity::class, $alias);
        return $rsm;
    }

    public static function resourceKind(EntityManagerInterface $em, string $alias = 'rk'): ResultSetMapping {
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(ResourceKind::class, $alias);
        return $rsm;
    }

    public static function user(EntityManagerInterface $em, string $alias = 'u'): ResultSetMapping {
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(UserEntity::class, $alias);
        return $rsm;
    }

    public static function scalar($columnName = 'count', $type = 'integer'): ResultSetMapping {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult($columnName, $columnName, $type);
        return $rsm;
    }

    public static function auditEntry(EntityManagerInterface $em, string $alias = 'ae') {
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(AuditEntry::class, $alias);
        return $rsm;
    }
}
