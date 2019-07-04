<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Service\UnauthenticatedUserPermissionHelper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SystemRoleVoter extends Voter {
    /** @var UnauthenticatedUserPermissionHelper */
    private $unauthenticatedUserPermissionHelper;

    public function __construct(UnauthenticatedUserPermissionHelper $unauthenticatedUserPermissionHelper) {
        $this->unauthenticatedUserPermissionHelper = $unauthenticatedUserPermissionHelper;
    }

    /** @inheritdoc */
    protected function supports($attribute, $subject) {
        return $attribute instanceof SystemRole;
    }

    /**
     * @inheritdoc
     * @param SystemRole $role
     */
    protected function voteOnAttribute($role, $subject, TokenInterface $token) {
        $user = $token->getUser();
        if (!$user || !($user instanceof UserEntity)) {
            $user = $this->unauthenticatedUserPermissionHelper->getUnauthenticatedUser();
        }
        if ($subject instanceof HasResourceClass) {
            $subject = $subject->getResourceClass();
        }
        $roleName = $role->roleName(is_string($subject) ? $subject : null);
        return $user->hasRole($roleName);
    }
}
