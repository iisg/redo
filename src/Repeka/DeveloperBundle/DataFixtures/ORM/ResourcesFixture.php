<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;

class ResourcesFixture extends RepekaFixture {
    const ORDER = ResourceKindsFixture::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ResourceKind $bookResourceKind */
        $bookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK);
        /** @var ResourceEntity $book1 */
        $book1 = $this->handleCommand(new ResourceCreateCommand($bookResourceKind, [
            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId() => 'PHP i MySQL',
            $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getId() => 'Błędy młodości...',
            $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getId() => 404
        ]));
        $this->handleCommand(new ResourceCreateCommand($bookResourceKind, [
            $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId() => 'PHP - to można leczyć!',
            $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getId() =>
                'Poradnik dla cierpiących na zwyrodnienie interpretera.',
            $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getId() => 1337,
            $this->getReference(MetadataFixture::REFERENCE_METADATA_SEE_ALSO)->getId() => $book1->getId()
        ]));
    }
}
