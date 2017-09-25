<?php
namespace Repeka\Application\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResultSetMappings {
    public static function resourceEntity(EntityManagerInterface $em): ResultSetMapping {
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata(ResourceEntity::class, 'w');
        return $rsm;
    }
}
