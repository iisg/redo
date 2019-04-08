<?php
namespace Repeka\Application\Security;

use Repeka\Application\Security\Voters\ResourceMetadataPermissionVoter;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class SecurityOracle {
    use CurrentUserAware;

    /** @var AccessDecisionManagerInterface */
    private $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager) {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    public function hasMetadataPermission(ResourceEntity $resource, $metadata, ?TokenInterface $userToken = null): bool {
        $permissionName = ResourceMetadataPermissionVoter::createMetadataPermissionName($metadata);
        return $this->hasPermission($resource, $permissionName, $userToken);
    }

    public function hasViewPermission(Identifiable $entity, ?TokenInterface $userToken = null) {
        return $this->hasPermission($entity, 'VIEW', $userToken);
    }

    public function hasPermission($subject, string $permissionName, ?TokenInterface $userToken = null): bool {
        if (!$userToken) {
            $userToken = $this->getCurrentUserToken();
        }
        return $this->accessDecisionManager->decide($userToken, [$permissionName], $subject);
    }
}
