<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\UserRoleHasNoUsageRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class UserRoleDeleteCommandValidator extends CommandAttributesValidator {
    /** @var UserRoleHasNoUsageRule */
    private $userRoleHasNoUsageRule;

    public function __construct(UserRoleHasNoUsageRule $userRoleHasNoUsageRule) {
        $this->userRoleHasNoUsageRule = $userRoleHasNoUsageRule;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute('userRole', $this->userRoleHasNoUsageRule);
    }
}
