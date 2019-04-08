<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowQuery;

class ResourceKindsFixture extends RepekaFixture {
    use ResourceKindsFixtureTrait;

    const ORDER = MetadataFixture::ORDER + ResourceWorkflowsFixture::ORDER;
    const REFERENCE_RESOURCE_KIND_BOOK = 'resource-kind-book';
    const REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK = 'resource-kind-forbidden-book';
    const REFERENCE_RESOURCE_KIND_CATEGORY = 'resource-kind-category';
    const REFERENCE_RESOURCE_KIND_DICTIONARY_DEPARTMENT = 'resource-kind-department';
    const REFERENCE_RESOURCE_KIND_DICTIONARY_UNIVERSITY = 'resource-kind-university';
    const REFERENCE_RESOURCE_KIND_DICTIONARY_PUBLISHING_HOUSE = 'resource-kind-publishing-house';
    const REFERENCE_RESOURCE_KIND_DICTIONARY_ALLOWED_ADDR_IP = 'resource-kind-allowed-addr-ip';
    const REFERENCE_RESOURCE_KIND_USER_GROUP = 'resource-kind-user-group';
    const REFERENCE_RESOURCE_KIND_CMS_STATIC_PAGE = 'resource-kind-cms-static-page';
    const REFERENCE_RESOURCE_KIND_CMS_CONFIG = 'resource-kind-cms-config';
    const REFERENCE_RESOURCE_KIND_CMS_REMARKS = 'resource-kind-cms-remarks';
    const REFERENCE_RESOURCE_KIND_CMS_REPORTED_REMARKS = 'resource-kind-cms-reported-remarks';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->addBooksResourceKinds();
        $this->addDictionariesResourceKinds();
        $this->addUserGroupResourceKind();
        $this->addCmsResourceKinds();
    }

    private function addBooksResourceKinds() {
        $bookWorkflow = $this
            ->handleCommand(new ResourceWorkflowQuery($this->getReference(ResourceWorkflowsFixture::BOOK_WORKFLOW)->getId()));
        $titleMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE)->getId();
        $titleLanguageMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE_LANGUAGE)->getId();
        $parentMetadata = $this->handleCommand(new MetadataGetQuery(-1));
        $labelDisplayStrategy = "[
{% for title in r|m{$titleMetadataId} %}
  {% set submetadata = []%}
  {% for title_language in title | sub{$titleLanguageMetadataId} %}
    {%  set submetadata = submetadata|merge(['{\"value\" : \"' ~ title_language ~ '\"}'])%}
  {% endfor %}
  {\"value\": \"{{title}}\", \"submetadata\": {\"-8\": [{{submetadata|join(',')}}]}},
{% endfor %}
]";
        $bookRK = $this->handleCommand(
            new ResourceKindCreateCommand(
                'book',
                ['PL' => 'Książka', 'EN' => 'Book',],
                [
                    $this->resourceLabelMetadata($labelDisplayStrategy),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_DESCRIPTION),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CREATION_DATE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_AUTHOR_LIFE_DATE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_HARD_COVER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_LANGUAGE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_SEE_ALSO),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_RELATED_BOOK),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_DIRECTORY),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE_PDF),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE_EPUB),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE_MOBI),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE_TXT),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_PUBLISHING_HOUSE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_SUPERVISOR),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_SUPERVISOR_USERNAME),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CREATOR),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_REAL_SCANNER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_SCANNER_USERNAME),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_TOP_PARENT_PATH),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_PARENT_PATH_LENGTH),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_RESOURCE_DOWNLOADS),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_URL),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_URL_LINK),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_RESOURCE_BIBTEX_TYPE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_RESOURCE_BIBTEX_KEY),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_RESOURCE_ORDER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_ACCESS_RIGHTS),
                ],
                false,
                $bookWorkflow
            ),
            self::REFERENCE_RESOURCE_KIND_BOOK
        );
        $forbiddenBookRK = $this->handleCommand(
            new ResourceKindCreateCommand(
                'forbidden_book',
                ['PL' => 'Zakazana książka', 'EN' => 'Forbidden book',],
                [
                    $this->resourceLabelMetadata($labelDisplayStrategy),
                    $parentMetadata->withOverrides([0 => $bookRK->getId()]),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_ISSUING_DEPARTMENT),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_PARENT_PATH_LENGTH),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK
        );
        $nameId = $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME)->getId();
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'category',
                [
                    'PL' => 'Kategoria',
                    'EN' => 'Category',
                ],
                [
                    $this->resourceLabelMetadata('{{r|m' . $nameId . '}}'),
                    SystemMetadata::PARENT()->toMetadata()->withOverrides(
                        [
                            'constraints' => [
                                'resourceKind' => [$bookRK->getId(), $forbiddenBookRK->getId()],
                            ],
                        ]
                    ),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_PARENT_PATH_LENGTH),
                ],
                true
            ),
            self::REFERENCE_RESOURCE_KIND_CATEGORY
        );
    }

    private function addDictionariesResourceKinds() {
        $nameMetadata = $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME);
        $abbrevMetadata = $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV);
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'department',
                [
                    'PL' => 'Wydział',
                    'EN' => 'Department',
                ],
                [
                    $this->resourceLabelMetadata("{{r|m{$nameMetadata->getId()}}} ({{r|m{$abbrevMetadata->getId()}}})"),
                    $nameMetadata,
                    $abbrevMetadata,
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_DICTIONARY_DEPARTMENT
        );
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'university',
                ['PL' => 'Uczelnia', 'EN' => 'University'],
                [
                    $this->resourceLabelMetadata("{{r|m{$nameMetadata->getId()}}} ({{r|m{$abbrevMetadata->getId()}}})"),
                    $nameMetadata->withOverrides(['label' => ['PL' => 'Nazwa uczelni']]),
                    $abbrevMetadata,
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_DICTIONARY_UNIVERSITY
        );
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'publishing_house',
                ['PL' => 'Wydawnictwo', 'EN' => 'Publishing house'],
                [
                    $this->resourceLabelMetadata("{{r|m{$nameMetadata->getId()}}}"),
                    $nameMetadata->withOverrides(['label' => ['PL' => 'Nazwa wydawnictwa']]),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_DICTIONARY_PUBLISHING_HOUSE
        );
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'dostep_dla_adresow_ip',
                ['PL' => 'Dostęp dla adresów IP', 'EN' => 'Allowed for IP addresses'],
                [
                    $this->resourceLabelMetadata("{{r|m{$nameMetadata->getId()}}}"),
                    $nameMetadata->withOverrides(['label' => ['PL' => 'Adresy IP']]),
                    $this->getReference(MetadataFixture::REFERENCE_METADATA_ALLOWED_ADDR_IP),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_DICTIONARY_ALLOWED_ADDR_IP
        );
    }

    private function addUserGroupResourceKind() {
        $nameMetadata = SystemMetadata::USERNAME()->toMetadata();
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'user_group',
                ['PL' => 'Grupa użytkowników', 'EN' => 'User group'],
                [
                    $this->resourceLabelMetadata("{{r|m{$nameMetadata->getName()}}} (ID: {{r.id}})"),
                    $nameMetadata,
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_USER_GROUP
        );
    }

    private function resourceLabelMetadata(string $displayStrategy): Metadata {
        return SystemMetadata::RESOURCE_LABEL()->toMetadata()->withOverrides(['displayStrategy' => $displayStrategy]);
    }

    private function addCmsResourceKinds() {
        $titleMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_TITLE)->getId();
        $configIdMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_ID)->getId();
        $remarkTitleMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_REMARK_TITLE)->getId();
        $remarkWorkflow = $this->handleCommand(
            new ResourceWorkflowQuery($this->getReference(ResourceWorkflowsFixture::REMARK_WORKFLOW)->getId())
        );
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'static_page',
                [
                    'PL' => 'Strona statyczna',
                    'EN' => 'Static page',
                ],
                [
                    $this->resourceLabelMetadata('{{r|m' . $titleMetadataId . '}}'),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_TITLE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_CONTENT),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_RENDERED_CONTENT),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_CMS_STATIC_PAGE
        );
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'cms_config',
                [
                    'PL' => 'Konfiguracja CMS',
                    'EN' => 'CMS Configuration',
                ],
                [
                    $this->resourceLabelMetadata('{{r|m' . $configIdMetadataId . '}}'),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_ID),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_CONFIG_VALUE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_CONTENT),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_CMS_CONFIG
        );
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'uwagi',
                [
                    'PL' => 'Uwagi',
                    'EN' => 'Remarks',
                ],
                [
                    $this->resourceLabelMetadata('{{r|m' . $titleMetadataId . '}}'),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_TITLE),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_CMS_REMARKS
        );
        $this->handleCommand(
            new ResourceKindCreateCommand(
                'zgloszona_uwaga',
                [
                    'PL' => 'Zgłoszona uwaga',
                    'EN' => 'Reported remark',
                ],
                [
                    $this->resourceLabelMetadata('{{r|m' . $remarkTitleMetadataId . '}}'),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_EMAIL_ADDRESS),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_REMARK_TITLE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_REMARK_CONTENT),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_REMARK_MANAGER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_REMARK_CREATION_DATE),
                ],
                false,
                $remarkWorkflow
            ),
            self::REFERENCE_RESOURCE_KIND_CMS_REPORTED_REMARKS
        );
    }
}
