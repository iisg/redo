<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var ContainsParentMetadataRule */
    private $containsParentMetadataRule;
    /** @var MetadataUpdateCommandValidator */
    private $metadataUpdateCommandValidator;
    /** @var ChildResourceKindsAreOfSameResourceClassRule */
    private $childResourceKindsAreOfSameResourceClassRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ContainsParentMetadataRule $containsParentMetadataRule,
        MetadataUpdateCommandValidator $metadataUpdateCommandValidator,
        ChildResourceKindsAreOfSameResourceClassRule $childResourceKindsAreOfSameResourceClassRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->containsParentMetadataRule = $containsParentMetadataRule;
        $this->childResourceKindsAreOfSameResourceClassRule = $childResourceKindsAreOfSameResourceClassRule;
        $this->metadataUpdateCommandValidator = $metadataUpdateCommandValidator;
    }

    /**
     * @param ResourceKindCreateCommand $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            // length 3 because Parent and Reproductor Metadata are obligatory and one chosen by user
            ->attribute(
                'metadataList',
                Validator::arrayType()
                    ->length(3)
                    ->each(Validator::instance(Metadata::class))
                    ->each(Validator::callback([$this, 'overrideMetadataValidator']))
                    ->callback([$this, 'allMetadataOfTheSameResourceClass'])
                    ->callback([$this, 'noMetadataDuplicates'])
            )
            ->attribute('metadataList', $this->containsParentMetadataRule)
            ->attribute('metadataList', $this->childResourceKindsAreOfSameResourceClassRule);
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
        $metadataUpdateCommand = new MetadataUpdateCommand(
            $metadata,
            $metadata->getLabel(),
            $metadata->getDescription(),
            $metadata->getPlaceholder(),
            $metadata->getConstraints(),
            $metadata->getGroupId(),
            $metadata->isShownInBrief(),
            $metadata->isCopiedToChildResource()
        );
        $this->metadataUpdateCommandValidator->getValidator($metadataUpdateCommand)->assert($metadataUpdateCommand);
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
