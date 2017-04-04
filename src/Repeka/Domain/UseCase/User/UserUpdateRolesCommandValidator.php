<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class UserUpdateRolesCommandValidator extends CommandAttributesValidator {
    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('user', Validator::instance(User::class)->callback(function (User $u) {
                return $u->getId() > 0;
            }))
            ->attribute('roles', Validator::arrayType()->each(Validator::instance(UserRole::class)));
    }
}
