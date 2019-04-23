<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Metadata\MetadataValueAdjuster\MetadataValueAdjusterComposite;
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
    /** @var MetadataValueAdjusterComposite */
    private $metadataValueAdjuster;

    public function __construct(
        MetadataConstraintManager $metadataConstraintManager,
        MetadataRepository $metadataRepository,
        ResourceContentsAdjuster $resourceContentsAdjuster,
        MetadataValueAdjusterComposite $metadataValueAdjuster
    ) {
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->metadataRepository = $metadataRepository;
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
        $this->metadataValueAdjuster = $metadataValueAdjuster;
    }

    /** @param MetadataConstraintCheckQuery $command */
    public function adjustCommand(Command $command): Command {
        $metadata = $this->toMetadata($command->getMetadata());
        $metadataValue = new MetadataValue($command->getValue());
        $metadataValue = $this->metadataValueAdjuster->adjustMetadataValue($metadataValue, $metadata);
        return new MetadataConstraintCheckQuery(
            $this->toMetadataConstraints($command->getConstraint()),
            $metadataValue->getValue(),
            $metadata,
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
