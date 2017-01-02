<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class UserUpdateStaticPermissionsCommandValidator extends CommandAttributesValidator {
    private $staticPermissions;

    public function __construct(array $staticPermissions) {
        $this->staticPermissions = $staticPermissions;
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('userId', Validator::intVal()->min(1))
            ->attribute('permissions', Validator::arrayType()->each(Validator::in($this->staticPermissions)));
    }
}
