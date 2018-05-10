<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Respect\Validation\Rules\AbstractRule;

class UserRoleHasNoUsageRule extends AbstractRule {
    /** @var UserRoleRepository */
    private $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository) {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function validate($input): bool {
        Assertion::isInstanceOf($input, UserRole::class);
        /** @var UserRole $input */
        $userRole = $this->userRoleRepository->findOne($input->getId());
        return $userRole->getUsers()->isEmpty();
    }
}
