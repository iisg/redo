<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataListByResourceClassQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;

/** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
class MetadataFixture extends RepekaFixture {
    use MetadataFixtureTrait;

    const ORDER = 1;

    const REFERENCE_METADATA_TITLE = 'metadata-title';
    const REFERENCE_METADATA_DESCRIPTION = 'metadata-description';
    const REFERENCE_METADATA_PUBLISH_DATE = 'metadata-publish-date';
    const REFERENCE_METADATA_HARD_COVER = 'metadata-hard-cover';
    const REFERENCE_METADATA_NO_OF_PAGES = 'metadata-no-of-pages';
    const REFERENCE_METADATA_LANGUAGE = 'metadata-language';
    const REFERENCE_METADATA_SEE_ALSO = 'metadata-see-also';
    const REFERENCE_METADATA_FILE = 'metadata-file';
    const REFERENCE_METADATA_CATEGORY_NAME = 'metadata-category-name';
    const REFERENCE_METADATA_ASSIGNED_SCANNER = 'metadata-assigned-scanner';
    const REFERENCE_METADATA_SUPERVISOR = 'metadata-supervisor';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
                'PL' => 'Tytuł książki',
                'EN' => 'The title of the book',
            ],
            'placeholder' => [
                'PL' => 'Znajdziesz go na okładce',
                'EN' => 'Find it on the cover',
            ],
            'control' => 'text',
            'shownInBrief' => true,
            'resourceClass' => 'books',
            'constraints' => $this->textConstraints(1),
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
            'resourceClass' => 'books',
            'constraints' => $this->constraints(3),
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
            'resourceClass' => 'books',
            'constraints' => $this->constraints(1),
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
            'resourceClass' => 'books',
            'constraints' => $this->constraints(1),
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
            'resourceClass' => 'books',
            'constraints' => $this->constraints(1),
        ]), self::REFERENCE_METADATA_NO_OF_PAGES);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Język',
            'label' => [
                'PL' => 'Język',
                'EN' => 'Language',
            ],
            'control' => 'text',
            'shownInBrief' => false,
            'resourceClass' => 'books',
            'constraints' => $this->textConstraints(1, '^[a-z]{3}$'),
        ]), self::REFERENCE_METADATA_LANGUAGE);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Zobacz też',
            'label' => [
                'PL' => 'Zobacz też',
                'EN' => 'See also',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'relationship',
            'resourceClass' => 'books',
            'constraints' => $this->relationshipConstraints(0),
        ]), self::REFERENCE_METADATA_SEE_ALSO);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Okładka',
            'label' => [
                'PL' => 'Okładka',
                'EN' => 'Cover',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'file',
            'resourceClass' => 'books',
            'constraints' => $this->constraints(0),
        ]), self::REFERENCE_METADATA_FILE);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Nazwa kategorii',
            'label' => [
                'PL' => 'Nazwa kategorii',
                'EN' => 'Category name',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'text',
            'resourceClass' => 'books',
            'constraints' => $this->textConstraints(1),
        ]), self::REFERENCE_METADATA_CATEGORY_NAME);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Skanista',
            'label' => [
                'PL' => 'Skanista',
                'EN' => 'Scanner',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'relationship',
            'resourceClass' => 'books',
            'constraints' => $this->relationshipConstraints(1, [-1]),
        ]), self::REFERENCE_METADATA_ASSIGNED_SCANNER);
        $addedMetadata[] = $this->handleCommand(MetadataCreateCommand::fromArray([
            'name' => 'Nadzorujący',
            'label' => [
                'PL' => 'Nadzorujący',
                'EN' => 'Supervisor',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'relationship',
            'resourceClass' => 'books',
            'constraints' => $this->relationshipConstraints(1, [-1]),
        ]), self::REFERENCE_METADATA_SUPERVISOR);
        $this->handleCommand(new MetadataUpdateOrderCommand(EntityUtils::mapToIds(
            $this->handleCommand(new MetadataListByResourceClassQuery('books'))
        ), 'books'));
    }
}
