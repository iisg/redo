<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;

/**
 * Stage 2: depends on metadata created in stage 2, see MetadataStage2Fixture for more explanations
 */
class ResourceKindsStage2Fixture extends RepekaFixture {
    use ResourceKindsFixtureTrait;

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
                $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE, 1, true),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_DESCRIPTION, 1, true),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_HARD_COVER),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_LANGUAGE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_SEE_ALSO),
                $this->metadata(MetadataStage2Fixture::REFERENCE_METADATA_RELATED_BOOK),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_SUPERVISOR),
            ],
            'books'
        ));
    }
}
