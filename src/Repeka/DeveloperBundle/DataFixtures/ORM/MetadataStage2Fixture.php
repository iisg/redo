<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;

/**
 * Stage 2: depends on resource kinds created in stage 1, which in turn depend on metadata created in stage 1.
 */
class MetadataStage2Fixture extends RepekaFixture {
    use MetadataFixtureTrait;

    const ORDER = ResourceKindsFixture::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->addRelationshipResourceKindConstraint(
            MetadataFixture::REFERENCE_METADATA_SEE_ALSO,
            ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK
        );
        $this->addRelationshipResourceKindConstraint(
            MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY,
            ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_UNIVERSITY,
            1
        );
        $this->addRelationshipResourceKindConstraint(
            MetadataFixture::REFERENCE_METADATA_ISSUING_DEPARTMENT,
            ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_DEPARTMENT
        );
    }

    private function addRelationshipResourceKindConstraint($metadataRef, $resourceKindRef, $maxCount = 0) {
        /** @var Metadata $metadata */
        $metadata = $this->handleCommand(new MetadataGetQuery($this->getReference($metadataRef)->getId()));
        $this->handleCommand(new MetadataUpdateCommand(
            $metadata->getId(),
            $metadata->getLabel(),
            $metadata->getDescription(),
            $metadata->getPlaceholder(),
            $this->relationshipConstraints($maxCount, [$this->getReference($resourceKindRef)->getId()]),
            $metadata->isShownInBrief(),
            $metadata->isCopiedToChildResource()
        ));
    }
}
