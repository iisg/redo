<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\MetadataRepository;

class ResourceTransitionCommandAdjuster implements CommandAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param ResourceTransitionCommand $command
     * @return ResourceTransitionCommand
     */
    public function adjustCommand(Command $command): Command {
        $newContents = $command->getContents()->mapAllValues(
            function ($value, int $metadataId) {
                $metadata = $this->metadataRepository->findOne($metadataId);
                return $this->replaceRelationshipResourcesWithIds($value, $metadata->getControl());
            }
        );
        $transition = $command->getTransition();
        $workflow = $command->getResource()->getKind()->getWorkflow();
        if (!$transition instanceof ResourceWorkflowTransition && $workflow !== null) {
            $resource = $command->getResource();
            $transition = $resource->getWorkflow()->getTransition($transition);
        }
        return new ResourceTransitionCommand($command->getResource(), $newContents, $transition, $command->getExecutor());
    }

    public function replaceRelationshipResourcesWithIds($value, MetadataControl $control) {
        return $control == MetadataControl::RELATIONSHIP() && $value instanceof ResourceEntity
            ? $value->getId()
            : $value;
    }
}
