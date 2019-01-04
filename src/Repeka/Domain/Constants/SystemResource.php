<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @method static SystemResource UNAUTHENTICATED_USER()
 */
class SystemResource extends Enum {

    const UNAUTHENTICATED_USER = -1;

    public function toResource(ResourceKind $resourceKind) {
        $value = $this->getValue();
        $resource = null;
        if ($value == self::UNAUTHENTICATED_USER) {
            $resource = new ResourceEntity($resourceKind, ResourceContents::empty());
        }
        /** @noinspection PhpUndefinedVariableInspection */
        Assertion::notNull($resource, "Not implemented: resource for value $value");
        EntityUtils::forceSetId($resource, $value);
        return $resource;
    }

    public function toUser() {
        $unauthenticatedUser = new UserEntity();
        $userResourceKind = SystemResourceKind::USER()->toResourceKind();
        $unauthenticatedUser->setUserData($this->toResource($userResourceKind));
        EntityUtils::forceSetId($unauthenticatedUser, SystemResource::UNAUTHENTICATED_USER);
        return $unauthenticatedUser;
    }
}
