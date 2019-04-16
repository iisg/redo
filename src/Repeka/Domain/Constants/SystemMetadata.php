<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @method static SystemMetadata PARENT()
 * @method static SystemMetadata USERNAME()
 * @method static SystemMetadata GROUP_MEMBER()
 * @method static SystemMetadata REPRODUCTOR()
 * @method static SystemMetadata RESOURCE_LABEL()
 * @method static SystemMetadata VISIBILITY()
 * @method static SystemMetadata TEASER_VISIBILITY()
 * @method static SystemMetadata RESOURCE_LABEL_LANGUAGE()
 */
class SystemMetadata extends Enum {
    const PARENT = -1;
    const USERNAME = -2;
    const GROUP_MEMBER = -3;
    const REPRODUCTOR = -4;
    const RESOURCE_LABEL = -5;
    const VISIBILITY = -6;
    const TEASER_VISIBILITY = -7;
    const RESOURCE_LABEL_LANGUAGE = -8;

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
                Metadata::DEFAULT_GROUP,
                null,
                true
            );
        } elseif ($value == self::GROUP_MEMBER) {
            $metadata = Metadata::create(
                SystemResourceClass::USER,
                MetadataControl::RELATIONSHIP(),
                'Group member',
                ['EN' => 'Group member', 'PL' => 'Członek grupy']
            );
        } elseif ($value == self::REPRODUCTOR) {
            $metadata = Metadata::create(
                '',
                MetadataControl::RELATIONSHIP(),
                'Reproductor',
                ['EN' => 'Reproductor', 'PL' => 'Reproduktor'],
                [],
                [],
                [],
                Metadata::DEFAULT_GROUP,
                null,
                false,
                true
            );
        } elseif ($value == self::RESOURCE_LABEL) {
            $metadata = Metadata::create(
                '',
                MetadataControl::TEXT(),
                'label',
                ['EN' => 'Label', 'PL' => 'Etykieta'],
                [],
                [],
                [],
                Metadata::DEFAULT_GROUP,
                '#{{ r.id }}',
                true
            );
        } elseif ($value == self::VISIBILITY) {
            $metadata = Metadata::create(
                '',
                MetadataControl::RELATIONSHIP(),
                'Visibility',
                ['EN' => 'Visibility', 'PL' => 'Widoczność']
            );
        } elseif ($value == self::TEASER_VISIBILITY) {
            $metadata = Metadata::create(
                '',
                MetadataControl::RELATIONSHIP(),
                'Teaser Visibility',
                ['EN' => 'Teaser visibility', 'PL' => 'Widoczność etykiety']
            );
        } elseif ($value == self::RESOURCE_LABEL_LANGUAGE) {
            $metadata = Metadata::create(
                '',
                MetadataControl::SYSTEM_LANGUAGE(),
                'label_language',
                ['EN' => 'Label language', 'PL' => 'Język Etykiety']
            );
            $parent = SystemMetadata::RESOURCE_LABEL()->toMetadata();
            $metadata->setParent($parent);
        }
        /** @noinspection PhpUndefinedVariableInspection */
        Assertion::notNull($metadata, "Not implemented: metadata for value $value");
        EntityUtils::forceSetId($metadata, $value);
        return $metadata;
    }
}
