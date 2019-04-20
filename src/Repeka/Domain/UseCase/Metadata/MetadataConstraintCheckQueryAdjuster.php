<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

class MetadataConstraintCheckQueryAdjuster implements CommandAdjuster {
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;

    public function __construct(
        MetadataConstraintManager $metadataConstraintManager,
        MetadataRepository $metadataRepository,
        ResourceContentsAdjuster $resourceContentsAdjuster
    ) {
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->metadataRepository = $metadataRepository;
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
    }

    /** @param MetadataConstraintCheckQuery $command */
    public function adjustCommand(Command $command): Command {
        return new MetadataConstraintCheckQuery(
            $this->toMetadataConstraints($command->getConstraint()),
            $command->getValue(),
            $this->toMetadata($command->getMetadata()),
            $command->getResource(),
            $this->toResourceContents($command->getCurrentContents())
        );
    }

    private function toMetadataConstraints($constraint): AbstractMetadataConstraint {
        return $constraint instanceof AbstractMetadataConstraint
            ? $constraint
            : $this->metadataConstraintManager->get($constraint);
    }

    private function toMetadata($metadata): Metadata {
        return $metadata instanceof Metadata
            ? $metadata
            : $this->metadataRepository->findByNameOrId($metadata);
    }

    private function toResourceContents($currentContents): ResourceContents {
        return $currentContents instanceof ResourceContents
            ? $currentContents
            : $this->resourceContentsAdjuster->adjust($currentContents);
    }
}
