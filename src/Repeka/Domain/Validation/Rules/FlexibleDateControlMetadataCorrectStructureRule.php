<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class FlexibleDateControlMetadataCorrectStructureRule extends AbstractRule {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKind */
    private $resourceKind;
    private $metadataDateControlModes;
    private $rangeModes;
    private const FLEXIBLE_DATE_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/';

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->metadataDateControlModes = MetadataDateControlMode::toArray();
        $this->rangeModes = MetadataDateControlMode::rangeModes();
    }

    public function forResourceKind($resourceKind): FlexibleDateControlMetadataCorrectStructureRule {
        $instance = new self($this->metadataRepository);
        $instance->resourceKind = $resourceKind;
        return $instance;
    }

    /** @param ResourceContents $contents */
    public function validate($contents) {
        Assertion::notNull(
            $this->resourceKind,
            'Resource kind not set. Use forResourceKind() to create validator for specific resource kind first.'
        );
        Assertion::isInstanceOf($contents, ResourceContents::class);
        $contents->forEachMetadata(
            function (array $values, int $metadataId) {
                $metadataKind = $this->resourceKind->hasMetadata($metadataId)
                    ? $this->resourceKind->getMetadataById($metadataId)
                    : $this->metadataRepository->findOne($metadataId);
                if ($metadataKind->getControl() == MetadataControl::FLEXIBLE_DATE()) {
                    $result = Validator::arrayType()->each(
                        Validator::allOf(
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
                            Validator::callback([$this, 'fromDateIsLowerThanTo'])
                        )
                    )->validate($values);
                    if (!$result) {
                        throw new \InvalidArgumentException('Date control metadata has invalid structure');
                    }
                }
            }
        );
        return true;
    }

    public function hasCustomDateFormat(string $date): bool {
        return preg_match(self::FLEXIBLE_DATE_REGEX, $date);
    }

    /**
     * @param array $value
     * @return bool
     */
    public function fromDateIsLowerThanTo($value) {
        $flexibleDate = FlexibleDate::fromArray($value);
        return strtotime($flexibleDate->getFrom()) <= strtotime($flexibleDate->getTo());
    }

    public function isRangeModeCorrect($mode) {
        if ($mode != null) {
            return Validator::in($this->rangeModes)->validate($mode);
        }
        return true;
    }
}
