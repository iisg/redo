<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var  ResourceClassExistsRule */
    private $resourceClassExistsRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ResourceClassExistsRule $resourceClassExistsRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    /**
     * @param ResourceKindCreateCommand $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('resourceClass', $this->resourceClassExistsRule)
            ->attribute('metadataList', Validator::arrayType()->length(1)->each(
                Validator::arrayType()->length(1)->key('baseId', Validator::intVal()->not(Validator::equals(SystemMetadata::PARENT)))
            ));
    }
}
