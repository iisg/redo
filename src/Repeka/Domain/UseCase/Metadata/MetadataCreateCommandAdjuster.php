<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Utils\StringUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Domain\Validation\Strippers\UnknownMetadataGroupStripper;

class MetadataCreateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    protected $unknownLanguageStripper;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var UnknownMetadataGroupStripper */
    protected $unknownMetadataGroupStripper;

    public function __construct(
        UnknownLanguageStripper $unknownLanguageStripper,
        MetadataConstraintManager $metadataConstraintManager,
        UnknownMetadataGroupStripper $unknownMetadataGroupStripper
    ) {
        $this->unknownLanguageStripper = $unknownLanguageStripper;
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->unknownMetadataGroupStripper = $unknownMetadataGroupStripper;
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
            $this->unknownMetadataGroupStripper->getSupportedMetadataGroup($command->getGroupId()),
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
