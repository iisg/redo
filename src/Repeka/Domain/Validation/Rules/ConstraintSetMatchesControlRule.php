<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Respect\Validation\Rules\AbstractRule;

class ConstraintSetMatchesControlRule extends AbstractRule {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;

    private $control;

    public function __construct(MetadataRepository $metadataRepository, MetadataConstraintManager $metadataConstraintManager) {
        $this->metadataRepository = $metadataRepository;
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
        $instance = new self($this->metadataRepository, $this->metadataConstraintManager);
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
        $actualKeys = array_keys($input);
        $requiredKeys = $this->metadataConstraintManager->getSupportedConstraintNamesForControl($this->control);
        $missingKeys = array_diff($requiredKeys, $actualKeys);
        $extraKeys = array_diff($actualKeys, $requiredKeys);
        return empty($missingKeys) && empty($extraKeys);
    }
}
