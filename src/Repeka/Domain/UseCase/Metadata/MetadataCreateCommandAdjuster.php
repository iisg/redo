<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Utils\StringUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class MetadataCreateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    protected $unknownLanguageStripper;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;

    public function __construct(UnknownLanguageStripper $unknownLanguageStripper, MetadataConstraintManager $metadataConstraintManager) {
        $this->unknownLanguageStripper = $unknownLanguageStripper;
        $this->metadataConstraintManager = $metadataConstraintManager;
    }

    /** @param MetadataCreateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new MetadataCreateCommand(
            StringUtils::normalizeEntityName($command->getName()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getLabel()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getDescription()),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getPlaceholder()),
            $command->getControlName(),
            $command->getResourceClass(),
            $this->clearUnsupportedConstraints($command->getControlName(), $command->getConstraints()),
            $command->getGroupId() ?: Metadata::DEFAULT_GROUP,
            $command->getDisplayStrategy(),
            $command->isShownInBrief(),
            $command->isCopiedToChildResource(),
            $command->getParent()
        );
    }

    protected function clearUnsupportedConstraints(string $control, array $constraints): array {
        $supportedConstraints = $this->metadataConstraintManager->getSupportedConstraintNamesForControl($control);
        return array_intersect_key($constraints, array_flip($supportedConstraints));
    }
}
