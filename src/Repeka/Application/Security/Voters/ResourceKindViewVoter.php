<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\UnauthenticatedUserPermissionHelper;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ResourceKindViewVoter extends Voter {

    /** @var UnauthenticatedUserPermissionHelper */
    private $unauthenticatedUserPermissionHelper;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(
        ResourceRepository $resourceRepository,
        UnauthenticatedUserPermissionHelper $unauthenticatedUserPermissionHelper
    ) {
        $this->resourceRepository = $resourceRepository;
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
        if ($user->hasRole(SystemRole::OPERATOR()->roleName())) {
            return true;
        }
        return $this->userCanSeeAnyResourcesOfKind($resourceKind, $user);
    }

    private function userCanSeeAnyResourcesOfKind($resourceKind, $user): bool {
        $query = ResourceListQuery::builder()
            ->filterByResourceKind($resourceKind)
            ->setExecutor($user)
            ->setPermissionMetadataId(SystemMetadata::VISIBILITY)
            ->setPage(1)
            ->setResultsPerPage(1)
            ->build();
        return $this->resourceRepository->findByQuery($query)->count() > 0;
    }
}
