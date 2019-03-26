<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\Constants\FileUploaderType;
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
    const REFERENCE_METADATA_AUTHOR_LIFE_DATE = 'metadata-author-life-date';
    const REFERENCE_METADATA_HARD_COVER = 'metadata-hard-cover';
    const REFERENCE_METADATA_NO_OF_PAGES = 'metadata-no-of-pages';
    const REFERENCE_METADATA_LANGUAGE = 'metadata-language';
    const REFERENCE_METADATA_SEE_ALSO = 'metadata-see-also';
    const REFERENCE_METADATA_FILE = 'metadata-file';
    const REFERENCE_METADATA_DIRECTORY = 'metadata-directory';
    const REFERENCE_METADATA_FILE_PDF = 'metadata-file-pdf';
    const REFERENCE_METADATA_FILE_EPUB = 'metadata-file-epub';
    const REFERENCE_METADATA_FILE_MOBI = 'metadata-file-mobi';
    const REFERENCE_METADATA_FILE_TXT = 'metadata-file-txt';
    const REFERENCE_METADATA_CATEGORY_NAME = 'metadata-category-name';
    const REFERENCE_METADATA_ASSIGNED_SCANNER = 'metadata-assigned-scanner';
    const REFERENCE_METADATA_REAL_SCANNER = 'metadata-real-scanner';
    const REFERENCE_METADATA_SCANNER_USERNAME = 'metadata-scanner-username';
    const REFERENCE_METADATA_SUPERVISOR = 'metadata-supervisor';
    const REFERENCE_METADATA_SUPERVISOR_USERNAME = 'metadata-supervisor-username';
    const REFERENCE_METADATA_TOP_PARENT_PATH = 'metadata-top-parent-path';
    const REFERENCE_METADATA_RELATED_BOOK = 'metadata-related-book';
    const REFERENCE_METADATA_PUBLISHING_HOUSE = 'metadata-publishing-house';
    const REFERENCE_METADATA_URL = 'metadata-url';
    const REFERENCE_METADATA_URL_LABEL = 'metadata-url-label';
    const REFERENCE_METADATA_URL_LINK = 'metadata-url-link';
    const REFERENCE_METADATA_CREATOR = 'metadata-creator';

    const REFERENCE_METADATA_CMS_TITLE = 'metadata-cms-name';
    const REFERENCE_METADATA_CMS_CONTENT = 'metadata-cms-template';
    const REFERENCE_METADATA_CMS_RENDERED_CONTENT = 'metadata-cms-rendered-content';
    const REFERENCE_METADATA_CMS_CONFIG_ID = 'metadata-cms-config-id';
    const REFERENCE_METADATA_CMS_CONFIG_VALUE = 'metadata-cms-config-value';

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
                    'constraints' => $this->constraints(1),
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
                    'name' => 'data_utworzenia_rekordu',
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
                    'name' => 'Życie autora',
                    'label' => [
                        'PL' => 'Życie autora',
                        'EN' => 'Author life',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'date-range',
                    'resourceClass' => 'books',
                    'constraints' => $this->constraints(1),
                ]
            ),
            self::REFERENCE_METADATA_AUTHOR_LIFE_DATE
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
                    'name' => 'pliki_strona_po_stronie',
                    'label' => [
                        'PL' => 'Pliki strona po stronie',
                        'EN' => 'Files for page by bage browsing',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'directory',
                    'resourceClass' => 'books',
                ]
            ),
            self::REFERENCE_METADATA_DIRECTORY
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'plik_pdf_podstawowy',
                    'label' => [
                        'PL' => 'Podstawowy plik PDF',
                        'EN' => 'Basic PDF file',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'file',
                    'resourceClass' => 'books',
                    'constraints' => $this->fileConstraint(1, FileUploaderType::FILE_MANAGER()),
                ]
            ),
            self::REFERENCE_METADATA_FILE_PDF
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'plik_epub',
                    'label' => [
                        'PL' => 'Plik EPUB',
                        'EN' => 'EPUB file',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'file',
                    'resourceClass' => 'books',
                    'constraints' => $this->fileConstraint(1, FileUploaderType::FILE_MANAGER()),
                ]
            ),
            self::REFERENCE_METADATA_FILE_EPUB
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'plik_mobi',
                    'label' => [
                        'PL' => 'Plik MOBI',
                        'EN' => 'MOBI file',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'file',
                    'resourceClass' => 'books',
                    'constraints' => $this->fileConstraint(1, FileUploaderType::FILE_MANAGER()),
                ]
            ),
            self::REFERENCE_METADATA_FILE_MOBI
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'plik_txt',
                    'label' => [
                        'PL' => 'Plik tekstowy',
                        'EN' => 'Text file',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'file',
                    'resourceClass' => 'books',
                    'constraints' => $this->fileConstraint(null, FileUploaderType::FILE_MANAGER()),
                ]
            ),
            self::REFERENCE_METADATA_FILE_TXT
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
                    'name' => 'osoba_tworzaca_rekord',
                    'label' => [
                        'PL' => 'Twórca zasobu',
                        'EN' => 'Resource creator',
                    ],
                    'description' => [],
                    'placeholder' => [],
                    'control' => 'relationship',
                    'resourceClass' => 'books',
                    'constraints' => $this->relationshipConstraints(1, [SystemResourceKind::USER]),
                ]
            ),
            self::REFERENCE_METADATA_CREATOR
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'Wydział',
                    'label' => [
                        'PL' => 'Wydział',
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
                    'control' => MetadataControl::TEXT(),
                    'shownInBrief' => false,
                    'resourceClass' => 'books',
                    'displayStrategy' => '{{ r | mSkanista | mUsername }}',
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
                    'control' => MetadataControl::TEXT(),
                    'shownInBrief' => false,
                    'resourceClass' => 'books',
                    'displayStrategy' => '{{ r | mNadzorujacy | mUsername }}',
                ]
            ),
            self::REFERENCE_METADATA_SUPERVISOR_USERNAME
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'parentPath',
                    'label' => [
                        'PL' => 'Ścieżka do top parenta',
                        'EN' => 'Top parent path',
                    ],
                    'control' => MetadataControl::RELATIONSHIP(),
                    'shownInBrief' => false,
                    'resourceClass' => 'books',
                    'displayStrategy' => <<<TEMPLATE
{% set ancestor = resource | metadata('parent') | first %}
[
{% for i in 0..9 if ancestor %}
    {{ ancestor.value }},
    {% set ancestor = ancestor | metadata('parent') | first %}
{% endfor %}
]
TEMPLATE
                    ,
                ]
            ),
            self::REFERENCE_METADATA_TOP_PARENT_PATH
        );
        $addedMetadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'urlWithLink',
                    'label' => [
                        'PL' => 'URL Link',
                        'EN' => 'URL Lint',
                    ],
                    'control' => MetadataControl::TEXT(),
                    'shownInBrief' => false,
                    'resourceClass' => 'books',
                    'displayStrategy' => <<<TEMPLATE
[
    {% for url in r|mUrl %}
        "<a href=\"{{url}}\">{{url|subUrlLabel}}</a>",
    {% endfor %}
]
TEMPLATE
                    ,
                ]
            ),
            self::REFERENCE_METADATA_URL_LINK
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
                    'control' => MetadataControl::TEXT(),
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
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'cmsConfigId',
                    'label' => [
                        'PL' => 'Klucz konfiguracji CMS',
                        'EN' => 'CMS Configuration id',
                    ],
                    'control' => MetadataControl::TEXT,
                    'resourceClass' => 'cms',
                    'constraints' => ['maxCount' => 1, 'uniqueInResourceClass' => true],
                ]
            ),
            self::REFERENCE_METADATA_CMS_CONFIG_ID
        );
        $metadata[] = $this->handleCommand(
            MetadataCreateCommand::fromArray(
                [
                    'name' => 'cmsConfigValue',
                    'label' => [
                        'PL' => 'Wartość konfiguracji CMS',
                        'EN' => 'CMS Configuration value',
                    ],
                    'control' => MetadataControl::TEXT,
                    'resourceClass' => 'cms',
                ]
            ),
            self::REFERENCE_METADATA_CMS_CONFIG_VALUE
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
                    'control' => MetadataControl::TEXT(),
                    'shownInBrief' => false,
                    'resourceClass' => 'cms',
                    'displayStrategy' => $staticPageContent,
                ]
            ),
            self::REFERENCE_METADATA_CMS_RENDERED_CONTENT
        );
    }
}