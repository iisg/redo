<?php
namespace Repeka\Application\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\ResourceEntity;

class RelationshipPropertyConverterListener {
    public function prePersist(LifecycleEventArgs $eventArgs) {
        $this->convertResourceContents($eventArgs->getEntity());
    }

    public function preUpdate(LifecycleEventArgs $eventArgs) {
        $this->convertResourceContents($eventArgs->getEntity());
    }

    private function convertResourceContents($entity) {
        if (!($entity instanceof ResourceEntity)) {
            return;
        }
        /** @var ResourceEntity $entity */
        $convertedContents = $this->convertRelationshipMetadata($entity->getContents());
        $entity->updateContents($convertedContents);
    }

    public function convertRelationshipMetadata(array $contents): array {
        foreach ($contents as &$values) {
            foreach ($values as &$value) {
                if ($value['value'] instanceof ResourceEntity) {
                    $value['value'] = $value['value']->getId();
                }
                if (isset($value['submetadata'])) {
                    $value['submetadata'] = $this->convertRelationshipMetadata($value['submetadata']);
                }
            }
        }
        return $contents;
    }
}
