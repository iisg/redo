<?php
namespace Repeka\Domain\Entity\MetadataDateControl;

use Assert\Assertion;
use DateTime;
use Exception;
use Repeka\Domain\Validation\Exceptions\FlexibleDateControlMetadataCorrectStructureRuleException;

class FlexibleDate {
    private $from;
    private $to;
    private $mode;
    private $rangeMode;
    private $displayValue;

    private static $dateFormats = [
        MetadataDateControlMode::DAY => 'd.m.Y',
        MetadataDateControlMode::DATE_TIME => 'd.m.Y, H:i:s',
        MetadataDateControlMode::MONTH => 'm.Y',
        MetadataDateControlMode::YEAR => 'Y',
    ];

    /**
     * @param string | null $from
     * @param string | null $to
     * @param string | MetadataDateControlMode | null $mode
     * @param string | MetadataDateControlMode $rangeMode
     */
    public function __construct($from, $to, $mode, $rangeMode = null) {
        $this->from = $from;
        $this->to = $to;
        $this->mode = $mode ?? MetadataDateControlMode::DATE_TIME;
        $this->rangeMode = $rangeMode;
        if ($mode == MetadataDateControlMode::RANGE) {
            Assertion::notNull($rangeMode);
        }
        $this->buildDisplayValue();
    }

    /** @return string | null */
    public function getFrom() {
        return $this->from;
    }

    /** @return string | null */
    public function getTo() {
        return $this->to;
    }

    /** @return string | MetadataDateControlMode */
    public function getMode() {
        return $this->mode;
    }

    /** @return string | MetadataDateControlMode | null */
    public function getRangeMode() {
        return $this->rangeMode;
    }

    private function buildDisplayValue() {
        try {
            if ($this->mode == MetadataDateControlMode::RANGE) {
                $this->displayValue = $this->getDate($this->getFrom(), $this->getRangeMode())
                    . ' - '
                    . $this->getDate($this->getTo(), $this->getRangeMode());
            } else {
                $this->displayValue = $this->getDate($this->getFrom(), $this->getMode());
            }
        } catch (Exception $e) {
            throw new FlexibleDateControlMetadataCorrectStructureRuleException();
        }
    }

    private function getDate($date, $mode): string {
        return !is_null($date) ? (new DateTime($date))->format(self::$dateFormats[$mode]) : '';
    }

    public function getDisplayValue() {
        return $this->displayValue;
    }

    public static function fromArray(array $data): FlexibleDate {
        return new FlexibleDate(
            $data['from'],
            $data['to'],
            $data['mode'],
            array_key_exists('rangeMode', $data) ? $data['rangeMode'] : null
        );
    }

    public function toArray() {
        return [
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'mode' => $this->getMode(),
            'rangeMode' => $this->getRangeMode(),
            'displayValue' => $this->getDisplayValue(),
        ];
    }
}
