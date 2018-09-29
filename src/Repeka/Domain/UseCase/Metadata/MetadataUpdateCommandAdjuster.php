<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class MetadataUpdateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    private $unknownLanguageStripper;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(UnknownLanguageStripper $unknownLanguageStripper, MetadataRepository $metadataRepository) {
        $this->unknownLanguageStripper = $unknownLanguageStripper;
        $this->metadataRepository = $metadataRepository;
    }

    /** @param MetadataUpdateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new MetadataUpdateCommand(
            $command->getMetadata() instanceof Metadata
                ? $command->getMetadata()
                : $this->metadataRepository->findOne($command->getMetadata()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewLabel()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewDescription()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewPlaceholder()),
            $command->getNewConstraints(),
            $command->getNewGroupId() ?: Metadata::DEFAULT_GROUP,
            $command->getNewShownInBrief(),
            $command->getNewCopyToChildResource()
        );
    }
}
