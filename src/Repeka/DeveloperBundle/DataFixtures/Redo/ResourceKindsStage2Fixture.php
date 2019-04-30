<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;

/**
 * Stage 2: depends on resource kinds created in stage 1.
 */
class ResourceKindsStage2Fixture extends RepekaFixture {
    const ORDER = ResourceKindsFixture::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        $this->updateRemarkResourceKind();
    }

    private function updateRemarkResourceKind() {
        /** @var ResourceKind $remarkRk */
        $remarksRk = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_REMARKS_REMARKS);
        $reportedRemarksRk = $this->getReference(ResourceKindsFixture::REFERENCE_RESOURCE_KIND_REMARK_REPORTED_REMARKS);
        $this->handleCommand(
            new ResourceKindUpdateCommand(
                $remarksRk,
                $remarksRk->getLabel(),
                array_merge(
                    $remarksRk->getMetadataList(),
                    [
                        SystemMetadata::PARENT()->toMetadata()->withOverrides(
                            [
                                'constraints' => [
                                    'resourceKind' => [$reportedRemarksRk->getId()],
                                ],
                            ]
                        ),
                    ]
                ),
                $remarksRk->isAllowedToClone(),
                $remarksRk->getWorkflow()
            )
        );
    }
}
