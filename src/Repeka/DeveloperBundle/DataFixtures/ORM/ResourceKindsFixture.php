<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;

class ResourceKindsFixture extends RepekaFixture {
    const ORDER = MetadataFixture::ORDER + ResourceWorkflowsFixture::ORDER;
    const REFERENCE_RESOURCE_KIND_BOOK = 'resource-kind-book';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $workflow = $this->container->get('repository.workflow')->findOne($this->getReference(ResourceWorkflowsFixture::BOOK_WORKFLOW));
        $this->handleCommand(new ResourceKindCreateCommand(
            [
                'PL' => 'Książka',
                'EN' => 'Book',
            ],
            [
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_DESCRIPTION)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_PUBLISH_DATE)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_HARD_COVER)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_NO_OF_PAGES)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_SEE_ALSO)->getId()],
                ['baseId' => $this->getReference(MetadataFixture::REFERENCE_METADATA_FILE)->getId()],
            ],
            $workflow
        ), self::REFERENCE_RESOURCE_KIND_BOOK);
    }
}
