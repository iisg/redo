<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @method static SystemResourceKind USER()
 */
class SystemResourceKind extends Enum {
    const USER = -1;

    public function toResourceKind(): ResourceKind {
        $value = $this->getValue();
        $resourceKind = null;
        if ($value == self::USER) {
            $resourceKind = new ResourceKind('user_kind', [], [SystemMetadata::USERNAME()->toMetadata()]);
        }
        Assertion::notNull($resourceKind, "Not implemented: resource kind for value $value");
        EntityUtils::forceSetId($resourceKind, $value);
        return $resourceKind;
    }
}
