<?php
namespace Repeka\Domain\Service;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Utils\EntityUtils;

class UnauthenticatedUserPermissionHelper {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function getUnauthenticatedUser(): UserEntity {
        $unauthenticatedUserData = $this->resourceRepository->findOne(SystemResource::UNAUTHENTICATED_USER);
        $unauthenticatedUser = new UserEntity();
        $unauthenticatedUser->setUserData($unauthenticatedUserData);
        EntityUtils::forceSetId($unauthenticatedUser, SystemResource::UNAUTHENTICATED_USER);
        return $unauthenticatedUser;
    }
}
