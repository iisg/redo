<?php
namespace Repeka\Application\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Container\ContainerInterface;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ResourceKindMetadataListConverterListener {
    use ContainerAwareTrait;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $eventArgs) {
        $this->convertMetadataListToOverrides($eventArgs->getEntity());
    }

    public function preUpdate(LifecycleEventArgs $eventArgs) {
        $this->convertMetadataListToOverrides($eventArgs->getEntity());
    }

    public function postLoad(LifecycleEventArgs $eventArgs) {
        $this->convertOverridesToMetadataList($eventArgs->getEntity());
    }

    private function convertMetadataListToOverrides($entity) {
        if ($entity instanceof ResourceKind) {
            $metadataList = $entity->getMetadataList();
            $metadataOverrides = [];
            foreach ($metadataList as $metadata) {
                $metadataOverrides[] = array_merge($metadata->getOverrides(), ['id' => $metadata->getId()]);
            }
            $entity->setMetadataOverrides($metadataOverrides);
        }
    }

    private function convertOverridesToMetadataList($entity) {
        if ($entity instanceof ResourceKind) {
            $metadataOverrides = $entity->getMetadataOverrides();
            $metadataIds = array_column($metadataOverrides, 'id');
            $metadataOverrides = array_combine($metadataIds, $metadataOverrides);
            $metadataRepository = $this->container->get(MetadataRepository::class);
            $metadataList = $metadataRepository->findByIds($metadataIds);
            foreach ($metadataList as $metadata) {
                $metadataWithOverrides = clone $metadata;
                EntityUtils::forceSetField($metadataWithOverrides, $metadataOverrides[$metadata->getId()], 'overrides');
                $metadataOverrides[$metadata->getId()] = $metadataWithOverrides;
            }
            $entity->setMetadataList(array_values($metadataOverrides));
        }
    }
}
