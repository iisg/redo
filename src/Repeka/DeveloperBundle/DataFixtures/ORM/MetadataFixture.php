<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;

class MetadataFixture extends RepekaFixture {
    const ORDER = LanguagesFixture::ORDER + 1;

    const REFERENCE_METADATA_TITLE = 'metadata-title';
    const REFERENCE_METADATA_DESCRIPTION = 'metadata-description';
    const REFERENCE_METADATA_PUBLISH_DATE = 'metadata-publish-date';
    const REFERENCE_METADATA_HARD_COVER = 'metadata-hard-cover';
    const REFERENCE_METADATA_NO_OF_PAGES = 'metadata-no-of-pages';
    const REFERENCE_METADATA_SEE_ALSO = 'metadata-see-also';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $addedMetadata = [];
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Tytuł',
            'label' => [
                'PL' => 'Tytuł',
                'EN' => 'Title',
            ],
            'description' => [
                'PL' => 'Tytuł zasobu',
                'EN' => 'The title of the resource',
            ],
            'placeholder' => [
                'PL' => 'Znajdziesz go na okładce',
                'EN' => 'Find it on the cover',
            ],
            'control' => 'text',
        ]), self::REFERENCE_METADATA_TITLE);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Opis',
            'label' => [
                'PL' => 'Opis',
                'EN' => 'Description',
            ],
            'description' => [
                'PL' => 'Napisz coś więcej',
                'EN' => 'Tell me more',
            ],
            'placeholder' => [],
            'control' => 'textarea',
        ]), self::REFERENCE_METADATA_DESCRIPTION);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Data wydania',
            'label' => [
                'PL' => 'Data wydania',
                'EN' => 'Publish date',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'date',
        ]), self::REFERENCE_METADATA_PUBLISH_DATE);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Czy ma twardą okładkę?',
            'label' => [
                'PL' => 'Twarda okładka',
                'EN' => 'Hard cover',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'boolean',
        ]), self::REFERENCE_METADATA_HARD_COVER);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Liczba stron',
            'label' => [
                'PL' => 'Liczba stron',
                'EN' => 'Number of pages',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'integer',
        ]), self::REFERENCE_METADATA_NO_OF_PAGES);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Zobacz też',
            'label' => [
                'PL' => 'Zobacz też',
                'EN' => 'See also'
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'relationship',
        ]), self::REFERENCE_METADATA_SEE_ALSO);
        $this->handleCommand(new MetadataUpdateOrderCommand(array_map(function (Metadata $metadata) {
            return $metadata->getId();
        }, $addedMetadata)));
    }
}
