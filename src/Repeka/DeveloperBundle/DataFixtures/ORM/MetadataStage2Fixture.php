<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListByResourceClassQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;

/**
 * Stage 2: depends on resource kinds created in stage 1, which in turn depend on metadata created in stage 1.
 */
class MetadataStage2Fixture extends RepekaFixture {
    use MetadataFixtureTrait;

    const ORDER = ResourceKindsFixture::ORDER + 1;

    const REFERENCE_METADATA_RELATED_BOOK = 'metadata-related-book';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Powiązana książka',
            'label' => [
                'PL' => 'Powiązana książka',
                'EN' => 'Related book',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'relationship',
            'shownInBrief' => true,
            'resourceClass' => 'books',
            'constraints' => $this->relationshipConstraints(0, [
                $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK)->getId(),
            ]),
        ]), self::REFERENCE_METADATA_RELATED_BOOK);
        /** @var Metadata $seeAlsoMetadata */
        $seeAlsoMetadata = $this->handleCommand(new MetadataGetQuery(
            $this->getReference(MetadataFixture::REFERENCE_METADATA_SEE_ALSO)->getId()
        ));
        $this->handleCommand(new MetadataUpdateCommand(
            $seeAlsoMetadata->getId(),
            $seeAlsoMetadata->getLabel(),
            $seeAlsoMetadata->getDescription(),
            $seeAlsoMetadata->getPlaceholder(),
            $this->relationshipConstraints(0, [
                $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_BOOK)->getId(),
            ]),
            $seeAlsoMetadata->isShownInBrief()
        ));
        $existingMetadata = $this->handleCommand(new MetadataListByResourceClassQuery('books'));
        $this->handleCommand(new MetadataUpdateOrderCommand(array_map(function (Metadata $metadata) {
            return $metadata->getId();
        }, $existingMetadata), 'books'));
    }
}
