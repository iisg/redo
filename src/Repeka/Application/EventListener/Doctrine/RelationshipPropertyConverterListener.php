<?php
namespace Repeka\Application\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Repeka\Domain\Entity\ResourceEntity;

class RelationshipPropertyConverterListener {
    public function prePersist(LifecycleEventArgs $eventArgs) {
        $this->convertResourceContents($eventArgs->getEntity());
    }

    public function preUpdate(LifecycleEventArgs $eventArgs) {
        $this->convertResourceContents($eventArgs->getEntity());
    }

    public function convertResourceContents($entity) {
        if (!($entity instanceof ResourceEntity)) {
            return;
        }
        /** @var ResourceEntity $entity */
        $convertedContents = $entity->getContents()->mapAllValues(function ($value) {
            if ($value instanceof ResourceEntity) {
                return $value->getId();
            } else {
                return $value;
            }
        });
        $entity->updateContents($convertedContents);
    }
}
