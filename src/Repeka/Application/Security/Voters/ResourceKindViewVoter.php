<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\Service\UnauthenticatedUserPermissionHelper;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ResourceKindViewVoter extends Voter {
    /** @var ReproductorPermissionHelper */
    private $reproductorPermissionHelper;
    /** @var UnauthenticatedUserPermissionHelper */
    private $unauthenticatedUserPermissionHelper;

    public function __construct(
        ReproductorPermissionHelper $reproductorPermissionHelper,
        UnauthenticatedUserPermissionHelper $unauthenticatedUserPermissionHelper
    ) {
        $this->reproductorPermissionHelper = $reproductorPermissionHelper;
        $this->unauthenticatedUserPermissionHelper = $unauthenticatedUserPermissionHelper;
    }

    protected function supports($attribute, $subject) {
        return $attribute === 'VIEW' && $subject instanceof ResourceKind;
    }

    /**
     * @inheritdoc
     * @param ResourceKind $resourceKind
     */
    public function voteOnAttribute($attribute, $resourceKind, TokenInterface $token) {
        $user = $token->getUser();
        if (!$user || !($user instanceof User)) {
            $user = $this->unauthenticatedUserPermissionHelper->getUnauthenticatedUser();
        }
        $allowedRkIds = EntityUtils::mapToIds($this->reproductorPermissionHelper->getResourceKindsWhichResourcesUserCanCreate($user));
        return in_array($resourceKind->getId(), $allowedRkIds);
    }
}
