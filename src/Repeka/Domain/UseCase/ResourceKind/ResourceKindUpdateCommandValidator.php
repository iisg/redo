<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends ResourceKindCreateCommandValidator {

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ContainsParentMetadataRule $containsParentMetadataRule,
        MetadataUpdateCommandValidator $metadataUpdateCommandValidator,
        ChildResourceKindsAreOfSameResourceClassRule $childResourceKindsAreOfSameResourceClassRule
    ) {
        parent::__construct(
            $notBlankInAllLanguagesRule,
            $containsParentMetadataRule,
            $metadataUpdateCommandValidator,
            $childResourceKindsAreOfSameResourceClassRule
        );
    }

    /** @param ResourceKindUpdateCommand $command */
    public function getValidator(Command $command): Validatable {
        $validator = parent::getValidator($command);
        if ($command->getResourceKind()->getWorkflow()) {
            $validator = $validator->attribute(
                'workflow',
                Validator::oneOf(Validator::nullType(), Validator::equals($command->getResourceKind()->getWorkflow()))
            );
        }
        return $validator;
    }
}
