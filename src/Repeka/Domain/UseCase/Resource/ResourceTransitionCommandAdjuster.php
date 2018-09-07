<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Metadata\MetadataValueAdjuster\MetadataValueAdjusterComposite;
use Repeka\Domain\Repository\MetadataRepository;

class ResourceTransitionCommandAdjuster implements CommandAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var MetadataValueAdjusterComposite */
    private $metadataValueAdjuster;

    public function __construct(MetadataRepository $metadataRepository, MetadataValueAdjusterComposite $metadataValueAdjuster) {
        $this->metadataRepository = $metadataRepository;
        $this->metadataValueAdjuster = $metadataValueAdjuster;
    }

    /**
     * @param ResourceTransitionCommand $command
     * @return ResourceTransitionCommand
     */
    public function adjustCommand(Command $command): Command {
        $newContents = $command->getContents()->mapAllValues([$this, 'adjustResourceContents']);
        $command->getResource()->updateContents(
            $command->getResource()->getContents()->mapAllValues([$this, 'adjustResourceContents'])
        );
        $transition = $command->getTransition();
        $workflow = $command->getResource()->getKind()->getWorkflow();
        if (!$transition instanceof ResourceWorkflowTransition && $workflow !== null) {
            $resource = $command->getResource();
            $transition = $resource->getWorkflow()->getTransition($transition);
        }
        return new ResourceTransitionCommand($command->getResource(), $newContents, $transition, $command->getExecutor());
    }

    public function adjustResourceContents(MetadataValue $value, int $metadataId) {
        $metadata = $this->metadataRepository->findOne($metadataId);
        return $this->metadataValueAdjuster->adjustMetadataValue($value, $metadata->getControl());
    }
}
