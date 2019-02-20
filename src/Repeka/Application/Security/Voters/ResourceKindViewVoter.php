<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ResourceKindViewVoter extends Voter {
    /** @var ReproductorPermissionHelper */
    private $reproductorPermissionHelper;

    public function __construct(ReproductorPermissionHelper $reproductorPermissionHelper) {
        $this->reproductorPermissionHelper = $reproductorPermissionHelper;
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
            $user = SystemResource::UNAUTHENTICATED_USER()->toUser();
        }
        $allowedRkIds = EntityUtils::mapToIds($this->reproductorPermissionHelper->getResourceKindsWhichResourcesUserCanCreate($user));
        return in_array($resourceKind->getId(), $allowedRkIds);
    }
}
