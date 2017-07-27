<?php
namespace Repeka\Domain\Constants;

use Assert\Assertion;
use MyCLabs\Enum\Enum;
use Repeka\Application\Entity\EntityUtils;
use Repeka\Domain\Entity\Metadata;

/**
 * @method static SystemMetadata PARENT()
 */
class SystemMetadata extends Enum {
    const PARENT = -1;
    const USERNAME = -2;

    public function toMetadata() {
        $value = $this->getValue();
        $metadata = null;
        if ($value == self::PARENT) {
            $metadata = Metadata::create('relationship', 'Parent', ['EN' => 'Parent', 'PL' => 'Rodzic',], '', [], [], [], true);
        } elseif ($value == self::USERNAME) {
            $metadata = Metadata::create(
                'text',
                'Username',
                ['EN' => 'Username', 'PL' => 'Nazwa użytkownika'],
                SystemResourceClass::USER,
                [],
                [],
                [],
                true
            );
        }
        /** @noinspection PhpUndefinedVariableInspection */
        Assertion::notNull($metadata, "Not implemented: metadata for value $value");
        EntityUtils::forceSetId($metadata, $value);
        return $metadata;
    }
}
