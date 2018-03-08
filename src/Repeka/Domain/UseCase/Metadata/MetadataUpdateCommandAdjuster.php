<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class MetadataUpdateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    private $unknownLanguageStripper;

    public function __construct(UnknownLanguageStripper $unknownLanguageStripper) {
        $this->unknownLanguageStripper = $unknownLanguageStripper;
    }

    /** @param MetadataUpdateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new MetadataUpdateCommand(
            $command->getMetadataId(),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewLabel()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewDescription()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getNewPlaceholder()),
            $command->getNewConstraints(),
            $command->getNewShownInBrief(),
            $command->getNewCopyToChildResource()
        );
    }
}
