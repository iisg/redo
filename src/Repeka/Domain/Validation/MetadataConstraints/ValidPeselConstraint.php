<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Respect\Validation\Validator;

class ValidPeselConstraint extends AbstractMetadataConstraint implements ConfigurableMetadataConstraint {

    private const CURRENT_CENTURY_MONTH_SCALE = 20;

    public function isConfigValid($config): bool {
        return Validator::boolType()->validate($config);
    }

    public function getSupportedControls(): array {
        return [MetadataControl::TEXT];
    }

    public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource = null) {
        $isPesel = $metadata->getConstraints()[$this->getConstraintName()] ?? false;
        if ($isPesel) {
            $this->validatePesel($metadataValue);
        }
    }

    public function validatePesel(string $pesel) {
        if (!Validator::pesel()->validate($pesel) || !$this->peselHasValidDate($pesel)) {
            throw new DomainException("'$pesel' is not a valid pesel number");
        }
    }

    private function peselHasValidDate(string $pesel): bool {
        $day = intval(substr($pesel, 4, 2));
        $month = intval(substr($pesel, 2, 2));
        $year = intval($this->getCentury($month) . substr($pesel, 0, 2));
        $month = $month <= 12 ?: $month - self::CURRENT_CENTURY_MONTH_SCALE;
        $time = mktime(0, 0, 0, $month, $day, $year);
        return $time && $time < time();
    }

    private function getCentury($month) {
        if (Validator::between(1, 12)->validate($month)) {
            return '19';
        } elseif (Validator::between(21, 32)->validate($month)) {
            return '20';
        }
        throw new DomainException("Invalid month in pesel number");
    }
}
