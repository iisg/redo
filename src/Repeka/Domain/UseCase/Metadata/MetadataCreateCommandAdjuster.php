<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class MetadataCreateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    private $unknownLanguageStripper;

    public function __construct(UnknownLanguageStripper $unknownLanguageStripper) {
        $this->unknownLanguageStripper = $unknownLanguageStripper;
    }

    /** @param MetadataCreateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new MetadataCreateCommand(
            Metadata::normalizeMetadataName($command->getName()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getLabel()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getDescription()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getPlaceholder()),
            $command->getControlName(),
            $command->getResourceClass(),
            $command->getConstraints(),
            $command->isShownInBrief(),
            $command->isCopiedToChildResource(),
            $command->getParent()
        );
    }
}
