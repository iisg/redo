<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Respect\Validation\Validator;

class DisplayStrategyConstraint extends RespectValidationMetadataConstraint implements ConfigurableMetadataConstraint {
    /** @var ResourceDisplayStrategyEvaluator */
    private $evaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $evaluator) {
        $this->evaluator = $evaluator;
    }

    public function getSupportedControls(): array {
        return [MetadataControl::DISPLAY_STRATEGY];
    }

    public function isConfigValid($displayStrategy): bool {
        if (!$displayStrategy || !is_string($displayStrategy)) {
            return false;
        }
        $this->evaluator->validateTemplate($displayStrategy);
        return true;
    }

    public function getValidator(Metadata $metadata, $metadataValue) {
        return Validator::alwaysValid();
    }
}
