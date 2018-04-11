<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

/**
 * @method static SystemMetadata PARENT()
 * @method static SystemMetadata USERNAME()
 * @method static SystemMetadata GROUP_MEMBER()
 */
class SystemMetadata extends Enum {
    const PARENT = -1;
    const USERNAME = -2;
    const GROUP_MEMBER = -3;

    public function toMetadata() {
        $value = $this->getValue();
        $metadata = null;
        if ($value == self::PARENT) {
            $metadata = Metadata::create(
                '',
                MetadataControl::RELATIONSHIP(),
                'Parent',
                ['EN' => 'Parent resource', 'PL' => 'Zasób nadrzędny']
            );
        } elseif ($value == self::USERNAME) {
            $metadata = Metadata::create(
                SystemResourceClass::USER,
                MetadataControl::TEXT(),
                'Username',
                ['EN' => 'Username', 'PL' => 'Nazwa użytkownika'],
                [],
                [],
                [],
                true
            );
        } elseif ($value == self::GROUP_MEMBER) {
            $metadata = Metadata::create(
                SystemResourceClass::USER,
                MetadataControl::RELATIONSHIP(),
                'Group member',
                ['EN' => 'Group member', 'PL' => 'Członek grupy'],
                [],
                [],
                ['resourceKind' => [SystemResourceKind::USER]]
            );
        }
        /** @noinspection PhpUndefinedVariableInspection */
        Assertion::notNull($metadata, "Not implemented: metadata for value $value");
        EntityUtils::forceSetId($metadata, $value);
        return $metadata;
    }
}
