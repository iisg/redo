<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandAdjuster;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var ContainsParentMetadataRule */
    private $containsParentMetadataRule;
    /** @var MetadataUpdateCommandValidator */
    private $metadataUpdateCommandValidator;
    /** @var ChildResourceKindsAreOfSameResourceClassRule */
    private $childResourceKindsAreOfSameResourceClassRule;
    /** @var MetadataUpdateCommandAdjuster */
    private $metadataUpdateCommandAdjuster;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ContainsParentMetadataRule $containsParentMetadataRule,
        MetadataUpdateCommandAdjuster $metadataUpdateCommandAdjuster,
        MetadataUpdateCommandValidator $metadataUpdateCommandValidator,
        ChildResourceKindsAreOfSameResourceClassRule $childResourceKindsAreOfSameResourceClassRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->containsParentMetadataRule = $containsParentMetadataRule;
        $this->childResourceKindsAreOfSameResourceClassRule = $childResourceKindsAreOfSameResourceClassRule;
        $this->metadataUpdateCommandValidator = $metadataUpdateCommandValidator;
        $this->metadataUpdateCommandAdjuster = $metadataUpdateCommandAdjuster;
    }

    /**
     * @param ResourceKindUpdateCommand $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        $validator = Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            // length 3 because Parent and Reproductor Metadata are obligatory and one chosen by user
            ->attribute(
                'metadataList',
                Validator::arrayType()
                    ->length(3)->setTemplate('length 3')
                    ->each(Validator::instance(Metadata::class))->setTemplate('allInstanceOfMetadata')
                    ->each(Validator::callback([$this, 'overrideMetadataValidator']))->setTemplate('overrideMetadataValidator')
                    ->callback([$this, 'allMetadataOfTheSameResourceClass'])->setTemplate('allMetadataOfTheSameResourceClass')
                    ->callback([$this, 'noMetadataDuplicates'])->setTemplate('noMetadataDuplicates')
            )
            ->attribute('metadataList', $this->containsParentMetadataRule)->setTemplate('containsParentMetadataRule')
            ->attribute('metadataList', $this->childResourceKindsAreOfSameResourceClassRule)
            ->setTemplate('childResourceKindsAreOfSameResourceClassRule')
            ->attribute('allowedToClone', Validator::boolVal());
        if (method_exists($command, 'getResourceKind') && $command->getResourceKind()->getWorkflow()) {
            $validator = $validator->attribute(
                'workflow',
                Validator::oneOf(Validator::nullType(), Validator::equals($command->getResourceKind()->getWorkflow()))
            );
        }
        return $validator;
    }

    /**
     * @param Metadata[] $metadata
     * @return bool
     */
    public function allMetadataOfTheSameResourceClass(array $metadataList): bool {
        $resourceClasses = array_filter(
            array_map(
                function (Metadata $metadata) {
                    return $metadata->getResourceClass();
                },
                $metadataList
            )
        );
        return count(array_unique($resourceClasses)) === 1;
    }

    public function overrideMetadataValidator(Metadata $metadata): bool {
        if ($metadata->getId() > 0) {
            $metadataUpdateCommand = new MetadataUpdateCommand(
                $metadata,
                $metadata->getLabel(),
                $metadata->getDescription(),
                $metadata->getPlaceholder(),
                $metadata->getConstraints(),
                $metadata->getGroupId(),
                $metadata->getDisplayStrategy(),
                $metadata->isShownInBrief(),
                $metadata->isCopiedToChildResource()
            );
            $metadataUpdateCommand = $this->metadataUpdateCommandAdjuster->adjustCommand($metadataUpdateCommand);
            $this->metadataUpdateCommandValidator
                ->getValidator($metadataUpdateCommand)
                ->setName($metadata->getName())
                ->assert($metadataUpdateCommand);
        }
        return true;
    }

    /**
     * @param Metadata[] $metadata
     * @return bool
     */
    public function noMetadataDuplicates(array $metadataList): bool {
        $metadataIds = EntityUtils::mapToIds($metadataList);
        return count(array_unique($metadataIds)) === count($metadataIds);
    }
}
