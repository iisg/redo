<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
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
        MetadataUpdateCommandValidator $metadataUpdateCommandValidator,
        ChildResourceKindsAreOfSameResourceClassRule $childResourceKindsAreOfSameResourceClassRule
    ) {
        parent::__construct(
            $notBlankInAllLanguagesRule,
            $correctResourceDisplayStrategyRule,
            $containsParentMetadataRule,
            $metadataUpdateCommandValidator,
            $childResourceKindsAreOfSameResourceClassRule
        );
        $this->rkConstraintIsUserIfNecessaryRule = $rkConstraintIsUserIfNecessaryRule;
    }

    /** @param ResourceKindUpdateCommand $command */
    public function getValidator(Command $command): Validatable {
        $validator = parent::getValidator($command)
            ->attribute(
                'metadataList',
                Validator::each(Validator::callback([$this, 'validateMetadata']))->setTemplate('Invalid constraints')
            );
        if ($command->getResourceKind()->getWorkflow()) {
            $validator = $validator->attribute(
                'workflow',
                Validator::oneOf(Validator::nullType(), Validator::equals($command->getResourceKind()->getWorkflow()))
            );
        }
        return $validator;
    }

    public function validateMetadata(Metadata $metadata): bool {
        return Validator::allOf(
            $this->rkConstraintIsUserIfNecessaryRule->forMetadata($metadata)
        )->validate($metadata->getConstraints());
    }
}
