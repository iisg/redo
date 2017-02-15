<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;

class ResourceKindsFixture extends RepekaFixture {
    const ORDER = MetadataFixture::ORDER + 1;
    const REFERENCE_RESOURCE_KIND_BOOK = 'resource-kind-book';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->handleCommand(ResourceKindCreateCommand::fromArray([
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
                ['base_id' => $this->getReference(MetadataFixture::REFERENCE_METADATA_SEE_ALSO)->getId()],
            ],
        ]), self::REFERENCE_RESOURCE_KIND_BOOK);
    }
}
