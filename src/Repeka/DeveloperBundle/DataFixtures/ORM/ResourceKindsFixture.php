<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowQuery;

class ResourceKindsFixture extends RepekaFixture {
    use ResourceKindsFixtureTrait;

    const ORDER = MetadataFixture::ORDER + ResourceWorkflowsFixture::ORDER;
    const REFERENCE_RESOURCE_KIND_BOOK = 'resource-kind-book';
    const REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK = 'resource-kind-forbidden-book';
    const REFERENCE_RESOURCE_KIND_CATEGORY = 'resource-kind-category';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $workflow = $this
            ->handleCommand(new ResourceWorkflowQuery($this->getReference(ResourceWorkflowsFixture::BOOK_WORKFLOW)->getId()));
        $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Książka',
                'EN' => 'Book',
            ],
            [
                ['baseId' => SystemMetadata::PARENT],
                $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE, 1, true),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_DESCRIPTION, 1, true),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_HARD_COVER),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_LANGUAGE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_SEE_ALSO, 0),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_FILE),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER),
                $this->metadata(MetadataFixture::REFERENCE_METADATA_SUPERVISOR),
            ],
            'books',
            [
                'header' => '{{m' . $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE)['baseId'] . '}}',
                'dropdown' => '{{m' . $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE)['baseId'] . '}} (ID: {{id}})',
            ],
            $workflow
        ), self::REFERENCE_RESOURCE_KIND_BOOK);
        $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Zakazana książka',
                'EN' => 'Forbidden book',
            ],
            [
                ['baseId' => SystemMetadata::PARENT],
                $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE, true),
            ],
            'books',
            [
                'header' => '{{m' . $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE)['baseId'] . '}}',
                'dropdown' => '{{m' . $this->metadata(MetadataFixture::REFERENCE_METADATA_TITLE)['baseId'] . '}} (ID: {{id}})',
            ]
        ), self::REFERENCE_RESOURCE_KIND_FORBIDDEN_BOOK);
        $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Kategoria',
                'EN' => 'Category',
            ],
            [
                ['baseId' => SystemMetadata::PARENT],
                $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME, true),
            ],
            'books',
            [
                'header' => '{{m' . $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME)['baseId'] . '}}',
                'dropdown' => '{{m' . $this->metadata(MetadataFixture::REFERENCE_METADATA_CATEGORY_NAME)['baseId'] . '}} (ID: {{id}})',
            ]
        ), self::REFERENCE_RESOURCE_KIND_CATEGORY);
    }
}
