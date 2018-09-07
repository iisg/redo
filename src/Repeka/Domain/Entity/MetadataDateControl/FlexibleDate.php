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
     * @param string | MetadataDateControlMode $mode
     * @param string | MetadataDateControlMode $rangeMode
     */
    public function __construct(string $from, string $to, $mode, $rangeMode = null) {
        $this->from = $from;
        $this->to = $to;
        $this->mode = $mode;
        $this->rangeMode = $rangeMode;
        if ($mode == MetadataDateControlMode::RANGE) {
            Assertion::notNull($rangeMode);
        }
        $this->buildDisplayValue();
    }

    public function getFrom(): string {
        return $this->from;
    }

    public function getTo(): string {
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
                $this->displayValue = (new DateTime($this->getFrom()))->format(self::$dateFormats[$this->getRangeMode()])
                    . ' - '
                    . (new DateTime($this->getTo()))->format(self::$dateFormats[$this->getRangeMode()]);
            } else {
                $this->displayValue = (new DateTime($this->getFrom()))->format(self::$dateFormats[$this->getMode()]);
            }
        } catch (Exception $e) {
            throw new FlexibleDateControlMetadataCorrectStructureRuleException();
        }
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
