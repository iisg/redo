<?php
namespace Repeka\Application\DoctrineListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Repeka\Domain\Entity\ResourceEntity;

class RelationshipPropertyConverterListener {
    public function prePersist(LifecycleEventArgs $eventArgs) {
        $this->convertRelationshipProperties($eventArgs->getEntity());
    }

    public function preUpdate(LifecycleEventArgs $eventArgs) {
        $this->convertRelationshipProperties($eventArgs->getEntity());
    }

    private function convertRelationshipProperties($entity) {
        if (!($entity instanceof ResourceEntity)) {
            return;
        }

        /** @var ResourceEntity $entity */
        $contents = $entity->getContents();
        foreach ($contents as &$value) {
            if ($value instanceof ResourceEntity) {
                $value = $value->getId();
            }
        }
        $entity->updateContents($contents);
    }
}
