<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validator;

class UserRoleCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;

    public function __construct(NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validator {
        return Validator::attribute('name', $this->notBlankInAllLanguagesRule);
    }
}
