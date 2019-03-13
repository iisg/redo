<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
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
            MetadataFixture::REFERENCE_METADATA_RELATED_BOOK,
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
        $this->addRelationshipResourceKindConstraint(
            MetadataFixture::REFERENCE_METADATA_PUBLISHING_HOUSE,
            ResourceKindsFixture::REFERENCE_RESOURCE_KIND_DICTIONARY_PUBLISHING_HOUSE
        );
        $this->addRelationshipResourceKindConstraint(
            MetadataFixture::REFERENCE_METADATA_SUPERVISOR,
            [SystemResourceKind::USER, ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP]
        );
        $this->addRelationshipResourceKindConstraint(
            MetadataFixture::REFERENCE_METADATA_CREATOR,
            [SystemResourceKind::USER, ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP]
        );
        $this->addRelationshipResourceKindConstraint(
            MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER,
            [SystemResourceKind::USER, ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP]
        );
        $this->addRelationshipResourceKindConstraint(
            SystemMetadata::GROUP_MEMBER,
            [ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP]
        );
        $this->addRelationshipResourceKindConstraint(
            SystemMetadata::REPRODUCTOR,
            [SystemResourceKind::USER, ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP]
        );
        $this->addRelationshipResourceKindConstraint(
            SystemMetadata::VISIBILITY,
            [SystemResourceKind::USER, ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP]
        );
        $this->addRelationshipResourceKindConstraint(
            SystemMetadata::TEASER_VISIBILITY,
            [SystemResourceKind::USER, ResourceKindsFixture::REFERENCE_RESOURCE_KIND_USER_GROUP]
        );
    }

    private function addRelationshipResourceKindConstraint($metadataRef, $resourceKindRefs, $maxCount = null) {
        if (!is_array($resourceKindRefs)) {
            $resourceKindRefs = [$resourceKindRefs];
        }
        $resourceKindIds = array_map(
            function ($ref) {
                return is_numeric($ref) ? $ref : $this->getReference($ref)->getId();
            },
            $resourceKindRefs
        );
        /** @var Metadata $metadata */
        $metadata = $this->handleCommand(
            new MetadataGetQuery(is_int($metadataRef) ? $metadataRef : $this->getReference($metadataRef)->getId())
        );
        $this->handleCommand(
            new MetadataUpdateCommand(
                $metadata->getId(),
                $metadata->getLabel(),
                $metadata->getDescription(),
                $metadata->getPlaceholder(),
                $this->relationshipConstraints($maxCount, $resourceKindIds),
                $metadata->getGroupId(),
                $metadata->getDisplayStrategy(),
                $metadata->isShownInBrief(),
                $metadata->isCopiedToChildResource()
            )
        );
    }
}
