<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Constants\SystemMetadata;
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

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->addBooksResourceKinds();
        $this->addDictionariesResourceKinds();
    }

    private function addBooksResourceKinds() {
        $workflow = $this
            ->handleCommand(new ResourceWorkflowQuery($this->getReference(ResourceWorkflowsFixture::BOOK_WORKFLOW)->getId()));
        $titleMetadataId = $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE)->getId();
        $parentMetadata = $this->handleCommand(new MetadataGetQuery(-1));
        $bookRK = $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Książka',
                'EN' => 'Book',
            ],
            [
                $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_DESCRIPTION),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_HARD_COVER),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_LANGUAGE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_SEE_ALSO),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_SUPERVISOR),
            ],
            [
                'header' => '{{allValues m' . $titleMetadataId . '}}',
                'dropdown' => '{{allValues m' . $titleMetadataId . '}} (ID: {{id}})',
            ],
            $workflow
        ), self::REFERENCE_RESOURCE_KIND_BOOK);
        $forbiddenBookRK = $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Zakazana książka',
                'EN' => 'Forbidden book',
            ],
            [
                $parentMetadata->withOverrides([0 => $bookRK->getId()]),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_ISSUING_DEPARTMENT),
            ],
            [
                'header' => '{{allValues m' . $titleMetadataId . '}}',
                'dropdown' => '{{allValues m' . $titleMetadataId . '}} (ID: {{id}})',
            ]
        ), self::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK);
        $nameId = $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME)->getId();
        $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Kategoria',
                'EN' => 'Category',
            ],
            [
                SystemMetadata::PARENT()->toMetadata()->withOverrides(['constraints' => [
                    'resourceKind' => [$bookRK->getId(), $forbiddenBookRK->getId()],
                ]]),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME),
            ],
            [
                'header' => '{{allValues m' . $nameId . '}}',
                'dropdown' => '{{allValues m' . $nameId . '}} (ID: {{id}})',
            ]
        ), self::REFERENCE_RESOURCE_KIND_CATEGORY);
    }

    private function addDictionariesResourceKinds() {
        $nameMetadata = $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_NAME);
        $abbrevMetadata = $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_ABBREV);
        $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Wydział',
                'EN' => 'Department',
            ],
            [
                $nameMetadata,
                $abbrevMetadata,
                $this->metadata(MetadataFixture::REFERENCE_METADATA_DEPARTMENTS_UNIVERSITY),
            ],
            [
                'header' => "{{allValues m{$nameMetadata->getId()}}} ({{allValues m{$abbrevMetadata->getId()}}})",
                'dropdown' => '{{allValues m' . $abbrevMetadata->getId() . '}} (ID: {{id}})',
            ]
        ), self::REFERENCE_RESOURCE_KIND_DICTIONARY_DEPARTMENT);
        $this->handleCommand(new ResourceKindCreateCommand(
            ['PL' => 'Uczelnia', 'EN' => 'University'],
            [
                $nameMetadata->withOverrides(['label' => ['PL' => 'Nazwa uczelni']]),
                $abbrevMetadata,
            ],
            [
                'header' => "{{allValues m{$nameMetadata->getId()}}} ({{allValues m{$abbrevMetadata->getId()}}})",
                'dropdown' => '{{allValues m' . $abbrevMetadata->getId() . '}} (ID: {{id}})',
            ]
        ), self::REFERENCE_RESOURCE_KIND_DICTIONARY_UNIVERSITY);
    }
}
