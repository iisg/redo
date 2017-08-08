<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;

    public function __construct(NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator::attribute('name', $this->notBlankInAllLanguagesRule);
    }
}
