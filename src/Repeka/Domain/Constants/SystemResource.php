<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @method static SystemResource UNAUTHENTICATED_USER()
 */
class SystemResource extends Enum {

    const UNAUTHENTICATED_USER = -1;

    public function toResource(ResourceKind $resourceKind) {
        $value = $this->getValue();
        $resource = null;
        if ($value == self::UNAUTHENTICATED_USER) {
            $resource = new ResourceEntity($resourceKind, ResourceContents::fromArray([SystemMetadata::USERNAME => '$$UNAUTHENTICATED$$']));
        }
        /** @noinspection PhpUndefinedVariableInspection */
        Assertion::notNull($resource, "Not implemented: resource for value $value");
        EntityUtils::forceSetId($resource, $value);
        return $resource;
    }
}
