<?php
namespace Repeka\Application\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;

class ResultSetMappings {
    public static function resourceEntity(EntityManagerInterface $em, string $alias = 'w'): ResultSetMapping {
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(ResourceEntity::class, $alias);
        return $rsm;
    }

    public static function user(EntityManagerInterface $em, string $alias = 'u'): ResultSetMapping {
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(UserEntity::class, $alias);
        return $rsm;
    }
}
