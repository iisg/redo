<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var CorrectResourceDisplayStrategySyntaxRule */
    private $correctResourceDisplayStrategySyntaxRule;
    /** @var ContainsParentMetadataRule */
    private $containsParentMetadataRule;
    /** @var ChildResourceKindsAreOfSameResourceClassRule */
    private $childResourceKindsAreOfSameResourceClassRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        CorrectResourceDisplayStrategySyntaxRule $correctResourceDisplayStrategySyntaxRule,
        ContainsParentMetadataRule $containsParentMetadataRule,
        ChildResourceKindsAreOfSameResourceClassRule $childResourceKindsAreOfSameResourceClassRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->correctResourceDisplayStrategySyntaxRule = $correctResourceDisplayStrategySyntaxRule;
        $this->containsParentMetadataRule = $containsParentMetadataRule;
        $this->childResourceKindsAreOfSameResourceClassRule = $childResourceKindsAreOfSameResourceClassRule;
    }

    /**
     * @param ResourceKindCreateCommand $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            // length 2 because Parent Metadata is obligatory and one chosen by user
            ->attribute(
                'metadataList',
                Validator::arrayType()
                    ->length(2)
                    ->each(Validator::instance(Metadata::class))
                    ->callback([$this, 'allMetadataOfTheSameResourceClass'])
                    ->callback([$this, 'noMetadataDuplicates'])
            )
            ->attribute('metadataList', $this->containsParentMetadataRule)
            ->attribute('metadataList', $this->childResourceKindsAreOfSameResourceClassRule)
            ->attribute('displayStrategies', Validator::arrayType()->each($this->correctResourceDisplayStrategySyntaxRule));
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

    /**
     * @param Metadata[] $metadata
     * @return bool
     */
    public function noMetadataDuplicates(array $metadataList): bool {
        $metadataIds = EntityUtils::mapToIds($metadataList);
        return count(array_unique($metadataIds)) === count($metadataIds);
    }
}
