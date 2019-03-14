<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\HasResourceClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SystemRoleVoter extends Voter {
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
            $user = SystemResource::UNAUTHENTICATED_USER()->toUser();
        }
        $roleName = $role->roleName($subject instanceof HasResourceClass ? $subject->getResourceClass() : null);
        return $user->hasRole($roleName);
    }
}
