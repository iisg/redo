<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceClassAdminVoter implements VoterInterface {
    public function vote(TokenInterface $token, $resource, array $attributes) {
        $metadataPermissions = array_filter($attributes, ResourceMetadataPermissionVoter::class . '::isMetadataPermission');
        if ($metadataPermissions) {
            $user = $token->getUser();
            if ($user instanceof UserEntity && $resource instanceof ResourceEntity) {
                if ($user->hasRole(SystemRole::ADMIN()->roleName($resource->getResourceClass()))) {
                    return self::ACCESS_GRANTED;
                }
            }
        }
        return self::ACCESS_ABSTAIN;
    }
}
