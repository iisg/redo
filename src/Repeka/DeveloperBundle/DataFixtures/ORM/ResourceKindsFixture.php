<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceKindsFixture extends RepekaFixture {
    const ORDER = MetadataFixture::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $container */
        $container = $this->container;
        $container->get('repeka.command_bus')->handle(ResourceKindCreateCommand::fromArray([
            'label' => [
                'PL' => 'Książka',
                'EN' => 'Book',
            ],
            'metadataList' => [
                ['base_id' => $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId()],
                ['base_id' => $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getId()],
                ['base_id' => $this->getReference(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE)->getId()],
                ['base_id' => $this->getReference(MetadataFixture::REFERENCE_METADATA_HARD_COVER)->getId()],
                ['base_id' => $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getId()],
            ],
        ]));
    }

    public function getOrder() {
        return self::ORDER;
    }
}
