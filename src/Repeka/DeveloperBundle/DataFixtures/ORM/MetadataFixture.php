<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\UseCase\Metadata\MetadataChildWithBaseCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;
use Repeka\Domain\Utils\EntityUtils;

/** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
class MetadataFixture extends RepekaFixture {
    use MetadataFixtureTrait;

    const ORDER = 1;

    const REFERENCE_METADATA_TITLE = 'metadata-title';
    const REFERENCE_METADATA_DESCRIPTION = 'metadata-description';
    const REFERENCE_METADATA_PUBLISH_DATE = 'metadata-publish-date';
    const REFERENCE_METADATA_CREATION_DATE = 'metadata-creation-date';
    const REFERENCE_METADATA_HARD_COVER = 'metadata-hard-cover';
    const REFERENCE_METADATA_NO_OF_PAGES = 'metadata-no-of-pages';
    const REFERENCE_METADATA_LANGUAGE = 'metadata-language';
    const REFERENCE_METADATA_SEE_ALSO = 'metadata-see-also';
    const REFERENCE_METADATA_FILE = 'metadata-file';
    const REFERENCE_METADATA_CATEGORY_NAME = 'metadata-category-name';
    const REFERENCE_METADATA_ASSIGNED_SCANNER = 'metadata-assigned-scanner';
    const REFERENCE_METADATA_REAL_SCANNER = 'metadata-real-scanner';
    const REFERENCE_METADATA_SCANNER_USERNAME = 'metadata-scanner-username';
    const REFERENCE_METADATA_SUPERVISOR = 'metadata-supervisor';
    const REFERENCE_METADATA_SUPERVISOR_USERNAME = 'metadata-supervisor-username';
    const REFERENCE_METADATA_RELATED_BOOK = 'metadata-related-book';
    const REFERENCE_METADATA_PUBLISHING_HOUSE = 'metadata-publishing-house';
    const REFERENCE_METADATA_URL = 'metadata-url';
    const REFERENCE_METADATA_URL_LABEL = 'metadata-url-label';

    const REFERENCE_METADATA_CMS_TITLE = 'metadata-cms-name';
    const REFERENCE_METADATA_CMS_CONTENT = 'metadata-cms-template';
    const REFERENCE_METADATA_CMS_RENDERED_CONTENT = 'metadata-cms-rendered-content';

    const REFERENCE_METADATA_DEPARTMENTS_NAME = 'metadata-departments-name';
    const REFERENCE_METADATA_DEPARTMENTS_ABBREV = 'metadata-departments-abbrev';
    const REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY = 'metadata-departments-university';
    const REFERENCE_METADATA_ISSUING_DEPARTMENT = 'metadata-issuing-department';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->addBooksMetadata();
        $this->addDepartmentsMetadata();
        $this->addCmsMetadata();
    }

    private function addBooksMetadata(): void {
        $addedMetadata = [];
        $addedMetadata[] = $titleMetadata = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
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
                    'copyToChildResource' => true,
                    'resourceClass' => 'books',
                    'constraints' => $this->textConstraints(1),
                ]
            ),
            self::REFERENCE_METADATA_TITLE
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
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
                ]
            ),
            self::REFERENCE_METADATA_DESCRIPTION
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Data wydania',
                    'label' => [
                        'PL' => 'Data wydania',
                        'EN' => 'Publish date',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'flexible-date',
                    'resourceClass' => 'books',
                    'constraints' => $this->constraints(1),
                ]
            ),
            self::REFERENCE_METADATA_PUBLISH_DATE
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Data utworzenia',
                    'label' => [
                        'PL' => 'Data utworzenia',
                        'EN' => 'Creation date',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'timestamp',
                    'resourceClass' => 'books',
                    'constraints' => $this->constraints(1),
                ]
            ),
            self::REFERENCE_METADATA_CREATION_DATE
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
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
                ]
            ),
            self::REFERENCE_METADATA_HARD_COVER
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
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
                ]
            ),
            self::REFERENCE_METADATA_NO_OF_PAGES
        );
        $addedMetadata[] = $languageMetadata = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Język',
                    'label' => [
                        'PL' => 'Język',
                        'EN' => 'Language',
                    ],
                    'control' => 'text',
                    'shownInBrief' => false,
                    'copyToChildResource' => true,
                    'resourceClass' => 'books',
                    'constraints' => $this->textConstraints(1, '^[a-z]{3}$'),
                ]
            ),
            self::REFERENCE_METADATA_LANGUAGE
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Zobacz też',
                    'label' => [
                        'PL' => 'Zobacz też',
                        'EN' => 'See also',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'relationship',
                    'resourceClass' => 'books',
                ]
            ),
            self::REFERENCE_METADATA_SEE_ALSO
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Okładka',
                    'label' => [
                        'PL' => 'Okładka',
                        'EN' => 'Cover',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'file',
                    'resourceClass' => 'books',
                ]
            ),
            self::REFERENCE_METADATA_FILE
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
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
                ]
            ),
            self::REFERENCE_METADATA_CATEGORY_NAME
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
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
                ]
            ),
            self::REFERENCE_METADATA_ASSIGNED_SCANNER
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Zeskanowane przez',
                    'label' => [
                        'PL' => 'Zeskanowane przez',
                        'EN' => 'Scanned by',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'relationship',
                    'resourceClass' => 'books',
                    'constraints' => $this->relationshipConstraints(1, [-1]),
                ]
            ),
            self::REFERENCE_METADATA_REAL_SCANNER
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Nadzorujący',
                    'label' => [
                        'PL' => 'Nadzorujący',
                        'EN' => 'Supervisor',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'relationship',
                    'resourceClass' => 'books',
                    'constraints' => $this->relationshipConstraints(1, [SystemResourceKind::USER]),
                ]
            ),
            self::REFERENCE_METADATA_SUPERVISOR
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Wydział wydający',
                    'label' => [
                        'PL' => 'Wydział wydający',
                        'EN' => 'Issued on',
                    ],
                    'control' => 'relationship',
                    'resourceClass' => 'books',
                ]
            ),
            self::REFERENCE_METADATA_ISSUING_DEPARTMENT
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Wydawnictwo',
                    'label' => [
                        'PL' => 'Wydawnictwo',
                        'EN' => 'Publishing house',
                    ],
                    'control' => 'relationship',
                    'resourceClass' => 'books',
                ]
            ),
            self::REFERENCE_METADATA_PUBLISHING_HOUSE
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
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
                ]
            ),
            self::REFERENCE_METADATA_RELATED_BOOK
        );
        $addedMetadata[] = $urlMetadata = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'url',
                    'label' => [
                        'PL' => 'URL',
                        'EN' => 'URL',
                    ],
                    'control' => MetadataControl::TEXT,
                    'resourceClass' => 'books',
                ]
            ),
            self::REFERENCE_METADATA_URL
        );
        $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'urlLabel',
                    'label' => [
                        'PL' => 'Tekst wyświetlany',
                        'EN' => 'URL display text',
                    ],
                    'control' => MetadataControl::TEXT,
                    'parent' => $urlMetadata,
                ]
            ),
            self::REFERENCE_METADATA_URL_LABEL
        );
        $this->handleCommand(
            new MetadataChildWithBaseCreateCommand(
                $titleMetadata,
                $languageMetadata,
                [
                    'label' => [
                        'PL' => 'Język tytułu',
                        'EN' => 'Title language',
                    ],
                    'constraints' => $this->textConstraints(1, '^[a-z]{3}$'),
                ]
            )
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'nazwaSkanisty',
                    'label' => [
                        'PL' => 'Username skanisty',
                        'EN' => 'Scanner username',
                    ],
                    'control' => MetadataControl::DISPLAY_STRATEGY(),
                    'shownInBrief' => false,
                    'resourceClass' => 'books',
                    'constraints' => ['displayStrategy' => '{{ r | mSkanista | mUsername }}'],
                ]
            ),
            self::REFERENCE_METADATA_SCANNER_USERNAME
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'nazwaNadzorujacego',
                    'label' => [
                        'PL' => 'Username nadzorującego',
                        'EN' => 'Supervisor username',
                    ],
                    'control' => MetadataControl::DISPLAY_STRATEGY(),
                    'shownInBrief' => false,
                    'resourceClass' => 'books',
                    'constraints' => ['displayStrategy' => '{{ r | mNadzorujacy | mUsername }}'],
                ]
            ),
            self::REFERENCE_METADATA_SUPERVISOR_USERNAME
        );
        $this->handleCommand(
            new MetadataUpdateOrderCommand(
                EntityUtils::mapToIds(
                    $this->handleCommand(MetadataListQuery::builder()->filterByResourceClass('books')->build())
                ),
                'books'
            )
        );
    }

    private function addDepartmentsMetadata() {
        $metadata = [];
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Nazwa',
                    'label' => [
                        'PL' => 'Nazwa',
                        'EN' => 'Name',
                    ],
                    'control' => 'text',
                    'shownInBrief' => false,
                    'resourceClass' => 'dictionaries',
                    'constraints' => $this->textConstraints(1),
                ]
            ),
            self::REFERENCE_METADATA_DEPARTMENTS_NAME
        );
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Skrót',
                    'label' => [
                        'PL' => 'Nazwa skrótowa',
                        'EN' => 'Abbreviation',
                    ],
                    'control' => 'text',
                    'shownInBrief' => true,
                    'resourceClass' => 'dictionaries',
                    'constraints' => ['regex' => '^[A-Z]{2,6}$'],
                ]
            ),
            self::REFERENCE_METADATA_DEPARTMENTS_ABBREV
        );
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Uczelnia',
                    'label' => [
                        'PL' => 'Uczelnia',
                        'EN' => 'University',
                    ],
                    'control' => 'relationship',
                    'shownInBrief' => false,
                    'resourceClass' => 'dictionaries',
                ]
            ),
            self::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY
        );
    }

    private function addCmsMetadata() {
        $metadata = [];
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'CMS Template',
                    'label' => [
                        'PL' => 'Szablon CMS',
                        'EN' => 'CMS Template',
                    ],
                    'control' => MetadataControl::DISPLAY_STRATEGY(),
                    'shownInBrief' => false,
                    'resourceClass' => 'cms',
                ]
            )
        );
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Tytuł strony',
                    'label' => [
                        'PL' => 'Tytuł strony',
                        'EN' => 'Page title',
                    ],
                    'control' => MetadataControl::TEXT,
                    'shownInBrief' => true,
                    'resourceClass' => 'cms',
                    'constraints' => $this->textConstraints(1),
                ]
            ),
            self::REFERENCE_METADATA_CMS_TITLE
        );
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Treść strony',
                    'label' => [
                        'PL' => 'Treść strony',
                        'EN' => 'Page content',
                    ],
                    'control' => MetadataControl::TEXTAREA,
                    'shownInBrief' => false,
                    'resourceClass' => 'cms',
                    'constraints' => $this->textConstraints(1),
                ]
            ),
            self::REFERENCE_METADATA_CMS_CONTENT
        );
        $staticPageContent = trim(
            <<<TEMPLATE
{% extends "redo/layout.twig" %}

{% set page_title = r | mTytul_Strony | first %}

{% block content %}
    {{ r | mTresc_Strony | raw }}
 {% endblock %}
TEMPLATE
        );
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Wyrenderowana treść strony',
                    'label' => [
                        'PL' => 'Wyrenderowana treść strony',
                        'EN' => 'Rendered page content',
                    ],
                    'control' => MetadataControl::DISPLAY_STRATEGY(),
                    'shownInBrief' => false,
                    'resourceClass' => 'cms',
                    'constraints' => ['displayStrategy' => $staticPageContent],
                ]
            ),
            self::REFERENCE_METADATA_CMS_RENDERED_CONTENT
        );
    }
}
