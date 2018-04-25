<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends ResourceKindCreateCommandValidator {
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule */
    private $rkConstraintIsUserIfNecessaryRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        CorrectResourceDisplayStrategySyntaxRule $correctResourceDisplayStrategyRule,
        ContainsParentMetadataRule $containsParentMetadataRule,
        ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule $rkConstraintIsUserIfNecessaryRule,
        ChildResourceKindsAreOfSameResourceClassRule $childResourceKindsAreOfSameResourceClassRule
    ) {
        parent::__construct(
            $notBlankInAllLanguagesRule,
            $correctResourceDisplayStrategyRule,
            $containsParentMetadataRule,
            $childResourceKindsAreOfSameResourceClassRule
        );
        $this->rkConstraintIsUserIfNecessaryRule = $rkConstraintIsUserIfNecessaryRule;
    }

    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return parent::getValidator($command)
            ->attribute('resourceKind', Validator::instance(ResourceKind::class))
            ->attribute(
                'metadataList',
                Validator::each(Validator::callback([$this, 'validateMetadata']))->setTemplate('Invalid constraints')
            );
    }

    public function validateMetadata(Metadata $metadata): bool {
        return Validator::allOf(
            $this->rkConstraintIsUserIfNecessaryRule->forMetadata($metadata)
        )->validate($metadata->getConstraints());
    }
}
