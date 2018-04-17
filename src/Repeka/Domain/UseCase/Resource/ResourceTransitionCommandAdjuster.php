<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceTransitionCommandAdjuster implements CommandAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceRepository $resourceRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @param ResourceTransitionCommand $command
     * @return ResourceTransitionCommand
     */
    public function adjustCommand(Command $command): Command {
        $newContents = $command->getContents()->mapAllValues(function ($value, int $metadataId) {
            $metadata = $this->metadataRepository->findOne($metadataId);
            return $this->replaceRelationshipIdsWithResources($value, $metadata->getControl());
        });
        $transition = $command->getTransition();
        $workflow = $command->getResource()->getKind()->getWorkflow();
        if (!$transition instanceof ResourceWorkflowTransition && $workflow !== null) {
            $resource = $command->getResource();
            $transition = $resource->getWorkflow()->getTransition($transition);
        }
        return new ResourceTransitionCommand($command->getResource(), $newContents, $transition, $command->getExecutor());
    }

    public function replaceRelationshipIdsWithResources($value, MetadataControl $control) {
        return $control == MetadataControl::RELATIONSHIP() && is_numeric($value)
            ? $this->resourceRepository->findOne($value)
            : $value;
    }
}
