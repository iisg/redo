<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class MetadataUpdateCommandAdjuster extends MetadataCreateCommandAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(
        UnknownLanguageStripper $unknownLanguageStripper,
        MetadataConstraintManager $metadataConstraintManager,
        MetadataRepository $metadataRepository
    ) {
        parent::__construct($unknownLanguageStripper, $metadataConstraintManager);
        $this->metadataRepository = $metadataRepository;
    }

    /** @param MetadataUpdateCommand $command */
    public function adjustCommand(Command $command): Command {
        $metadata = $command->getMetadata() instanceof Metadata
            ? $command->getMetadata()
            : $this->metadataRepository->findOne($command->getMetadata());
        return new MetadataUpdateCommand(
            $metadata,
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewLabel()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewDescription()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewPlaceholder()),
            $this->clearUnsupportedConstraints($metadata->getControl()->getValue(), $command->getNewConstraints()),
            $command->getNewGroupId() ?: Metadata::DEFAULT_GROUP,
            $command->getNewDisplayStrategy(),
            $command->getNewShownInBrief(),
            $command->getNewCopyToChildResource()
        );
    }
}
