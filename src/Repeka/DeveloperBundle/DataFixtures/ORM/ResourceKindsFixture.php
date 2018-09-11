<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
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
    const REFERENCE_RESOURCE_KIND_USER_GROUP = 'resource-kind-user-group';
    const REFERENCE_RESOURCE_KIND_CMS_STATIC_PAGE = 'resource-kind-cms-static-page';

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
        $workflow = $this
            ->handleCommand(new ResourceWorkflowQuery($this->getReference(ResourceWorkflowsFixture::BOOK_WORKFLOW)->getId()));
        $titleMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE)->getId();
        $parentMetadata = $this->handleCommand(new MetadataGetQuery(-1));
        $bookRK = $this->handleCommand(
            new ResourceKindCreateCommand(
                [
                    'PL' => 'Książka',
                    'EN' => 'Book',
                ],
                [
                    $this->resourceLabelMetadata('{{r|m' . $titleMetadataId . '}}'),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_DESCRIPTION),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_CREATION_DATE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_HARD_COVER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_LANGUAGE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_SEE_ALSO),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_RELATED_BOOK),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_PUBLISHING_HOUSE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_SUPERVISOR),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_REAL_SCANNER),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_URL),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_DETAILS_PAGE),
                ],
                $workflow
            ),
            self::REFERENCE_RESOURCE_KIND_BOOK
        );
        $forbiddenBookRK = $this->handleCommand(
            new ResourceKindCreateCommand(
                [
                    'PL' => 'Zakazana książka',
                    'EN' => 'Forbidden book',
                ],
                [
                    $this->resourceLabelMetadata('{{r|m' . $titleMetadataId . '}}'),
                    $parentMetadata->withOverrides([0 => $bookRK->getId()]),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_ISSUING_DEPARTMENT),
                    $this->metadata(MetadataFixture::REFERENCE_METADATA_DETAILS_PAGE),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK
        );
        $nameId = $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME)->getId();
        $this->handleCommand(
            new ResourceKindCreateCommand(
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
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_CATEGORY
        );
    }

    private function addDictionariesResourceKinds() {
        $nameMetadata = $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME);
        $abbrevMetadata = $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV);
        $this->handleCommand(
            new ResourceKindCreateCommand(
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
                ['PL' => 'Wydawnictwo', 'EN' => 'Publishing house'],
                [
                    $this->resourceLabelMetadata("{{r|m{$nameMetadata->getId()}}}"),
                    $nameMetadata->withOverrides(['label' => ['PL' => 'Nazwa wydawnictwa']]),
                ]
            ),
            self::REFERENCE_RESOURCE_KIND_DICTIONARY_PUBLISHING_HOUSE
        );
    }

    private function addUserGroupResourceKind() {
        $nameMetadata = SystemMetadata::USERNAME()->toMetadata();
        $this->handleCommand(
            new ResourceKindCreateCommand(
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
        return SystemMetadata::RESOURCE_LABEL()->toMetadata()->withOverrides(['constraints' => ['displayStrategy' => $displayStrategy]]);
    }

    private function addCmsResourceKinds() {
        $titleMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_CMS_TITLE)->getId();
        $this->handleCommand(
            new ResourceKindCreateCommand(
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
    }
}
