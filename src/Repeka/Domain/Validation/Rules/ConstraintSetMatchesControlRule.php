<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Respect\Validation\Rules\AbstractRule;

class ConstraintSetMatchesControlRule extends AbstractRule {
    /** @var MetadataRepository */
    private $metadataRepository;

    private $control;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param string|MetadataControl $control
     */
    public function forControl($control): ConstraintSetMatchesControlRule {
        if (is_string($control)) {
            $control = new MetadataControl($control);
        }
        if (!($control instanceof MetadataControl)) {
            throw new \InvalidArgumentException('Argument must be a valid control name or a control instance');
        }
        $instance = new self($this->metadataRepository);
        $instance->control = $control;
        return $instance;
    }

    public function forMetadataId(int $metadataId): ConstraintSetMatchesControlRule {
        $control = $this->metadataRepository->findOne($metadataId)->getControl();
        return $this->forControl($control);
    }

    public function validate($input) {
        Assertion::notNull(
            $this->control,
            'Control not set. Use forControl() or forMetadataId() to create validator for specific control first.'
        );
        $keys = array_keys($input);
        if ($this->control == MetadataControl::RELATIONSHIP()) {
            return $keys == ['resourceKind'];
        } else {
            return count($keys) == 0;
        }
    }
}
