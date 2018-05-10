<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class UserUpdateRolesCommandValidator extends CommandAttributesValidator {
    /**
     * @param UserUpdateRolesCommand $command
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute(
                'user',
                Validator::callback(
                    function (User $u) {
                        return $u->getId() > 0;
                    }
                )
            )
            ->attribute(
                'roles',
                Validator::arrayType()
                    ->each(Validator::instance(UserRole::class))
                    ->callback($this->doesNotRevokeSystemRolesFromMyself($command))
                    ->setTemplate('You cannot revoke any of the system roles from yourself')
            );
    }

    public function doesNotRevokeSystemRolesFromMyself(UserUpdateRolesCommand $command) {
        return function () use ($command) {
            if ($command->getUser()->getId() != $command->getExecutor()->getId()) {
                return true;
            }
            $currentSystemRoles = array_filter(
                $command->getUser()->getUserRoles(),
                function (UserRole $userRole) {
                    return $userRole->isSystemRole();
                }
            );
            $currentSystemRoleIds = EntityUtils::mapToIds($currentSystemRoles);
            $newRoleIds = EntityUtils::mapToIds($command->getRoles());
            return count(array_diff($currentSystemRoleIds, $newRoleIds)) === 0;
        };
    }
}
