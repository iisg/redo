<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\HasResourceClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceClassAdminVoter implements VoterInterface {
    public function vote(TokenInterface $token, $subject, array $attributes) {
        $metadataPermissions = array_filter($attributes, ResourceMetadataPermissionVoter::class . '::isMetadataPermission');
        $viewPermission = in_array('VIEW', $attributes);
        $fileDownloadPermission = in_array(FileDownloadVoter::FILE_DOWNLOAD_ATTRIBUTE, $attributes);
        if ($metadataPermissions || $viewPermission || $fileDownloadPermission) {
            $user = $token->getUser();
            $resource = $subject instanceof HasResourceClass ? $subject : $subject['resource'];
            if ($user instanceof UserEntity && $resource instanceof HasResourceClass) {
                if ($user->hasRole(SystemRole::ADMIN()->roleName($resource->getResourceClass()))) {
                    return self::ACCESS_GRANTED;
                }
            }
        }
        return self::ACCESS_ABSTAIN;
    }
}
