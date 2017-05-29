<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;

/**
 * Stage 2: depends on metadata created in stage 2, see MetadataStage2Fixture for more explanations
 */
class ResourceKindsStage2Fixture extends RepekaFixture {
    const ORDER = MetadataStage2Fixture::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ResourceKind $bookResourceKind */
        $bookResourceKind = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK);
        $this->handleCommand(new ResourceKindUpdateCommand(
            $bookResourceKind->getId(),
            $bookResourceKind->getLabel(),
            [
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_HARD_COVER)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_SEE_ALSO)->getId()],
                ['baseId' => $this->getReference(MetadataStage2Fixture::REFERENCE_METADATA_RELATED_BOOK)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_FILE)->getId()],
            ]
        ));
    }
}
