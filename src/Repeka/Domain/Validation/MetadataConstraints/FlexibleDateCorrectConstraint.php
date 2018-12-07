<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Respect\Validation\Validator;

class FlexibleDateCorrectConstraint extends RespectValidationMetadataConstraint {
    private $metadataDateControlModes;
    private $rangeModes;
    private const FLEXIBLE_DATE_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/';

    public function __construct() {
        $this->rangeModes = MetadataDateControlMode::rangeModes();
        $this->metadataDateControlModes = MetadataDateControlMode::toArray();
    }

    public function getSupportedControls(): array {
        return [MetadataControl::FLEXIBLE_DATE, MetadataControl::DATE_RANGE];
    }

    protected function getValidator(Metadata $metadata, $metadataValue) {
        return Validator::allOf(
            Validator::keySet(
                Validator::key('from', Validator::callback([$this, 'hasCustomDateFormat'])),
                Validator::key('to', Validator::callback([$this, 'hasCustomDateFormat'])),
                Validator::key(
                    'mode',
                    Validator::in($this->metadataDateControlModes)->setTemplate('date mode is not correct')
                ),
                Validator::key('rangeMode', Validator::callback([$this, 'isRangeModeCorrect'])),
                Validator::key('displayValue')
            ),
            Validator::callback([$this, 'fromDateIsLowerThanTo']),
            Validator::callback([$this, 'atLeastOneDateProvided'])
        );
    }

    /**
     * @param string | null $date
     * @return bool
     */
    public function hasCustomDateFormat($date) {
        return is_null($date) ? true : preg_match(self::FLEXIBLE_DATE_REGEX, $date);
    }

    /**
     * @param array $value
     * @return bool
     */
    public function fromDateIsLowerThanTo($value) {
        $flexibleDate = FlexibleDate::fromArray($value);
        if (is_null($flexibleDate->getFrom()) || is_null($flexibleDate->getTo())) {
            return true;
        }
        return strtotime($flexibleDate->getFrom()) <= strtotime($flexibleDate->getTo());
    }

    /**
     * @param array $value
     * @return bool
     */
    public function atLeastOneDateProvided($value) {
        $flexibleDate = FlexibleDate::fromArray($value);
        return !is_null($flexibleDate->getFrom()) || !is_null($flexibleDate->getTo());
    }

    public function isRangeModeCorrect($mode) {
        if ($mode != null) {
            return Validator::in($this->rangeModes)->validate($mode);
        }
        return true;
    }
}
