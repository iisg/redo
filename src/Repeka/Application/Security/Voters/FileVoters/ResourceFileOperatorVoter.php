<?php
namespace Repeka\Application\Security\Voters\FileVoters;

use Repeka\Application\Security\Voters\FileDownloadVoter;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileOperatorVoter implements VoterInterface {
    public function vote(TokenInterface $token, $subject, array $attributes) {
        $fileDownloadPermission = in_array(FileDownloadVoter::FILE_DOWNLOAD_ATTRIBUTE, $attributes);
        $resource = $subject instanceof HasResourceClass ? $subject : (is_array($subject) ? $subject['resource'] : null);
        $user = $token->getUser();
        if ($fileDownloadPermission && $user instanceof User && $resource instanceof ResourceEntity && $resource->isVisibleFor($user)) {
            if ($resource instanceof HasResourceClass) {
                if ($user->hasRole(SystemRole::OPERATOR()->roleName($resource->getResourceClass()))) {
                    return self::ACCESS_GRANTED;
                }
            }
        }
        return self::ACCESS_ABSTAIN;
    }
}
