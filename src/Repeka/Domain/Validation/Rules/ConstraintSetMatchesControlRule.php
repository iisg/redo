<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Respect\Validation\Rules\AbstractRule;

class ConstraintSetMatchesControlRule extends AbstractRule {
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;

    private $control;

    public function __construct(MetadataConstraintManager $metadataConstraintManager) {
        $this->metadataConstraintManager = $metadataConstraintManager;
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
        $instance = new self($this->metadataConstraintManager);
        $instance->control = $control;
        return $instance;
    }

    public function forMetadata(Metadata $metadata): ConstraintSetMatchesControlRule {
        return $this->forControl($metadata->getControl());
    }

    public function validate($input) {
        Assertion::notNull(
            $this->control,
            'Control not set. Use forControl() or forMetadataId() to create validator for specific control first.'
        );
        $actualKeys = array_keys($input);
        $controlKeys = $this->metadataConstraintManager->getSupportedConstraintNamesForControl($this->control);
        $extraKeys = array_diff($actualKeys, $controlKeys);
        return empty($extraKeys);
    }
}
