<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Repeka\Domain\Entity\MetadataControl;
use Symfony\Component\HttpFoundation\Request;

class MetadataValueProcessor {
    /** @var MetadataValueProcessorStrategy[] */
    private $strategies = [];

    public function process(array $values, MetadataControl $control, Request $request): array {
        return array_key_exists($control->getValue(), $this->strategies)
            ? $this->strategies[$control->getValue()]->processValues($values, $request)
            : $values;
    }

    public function registerStrategy(MetadataValueProcessorStrategy $strategy) {
        $control = $strategy->getSupportedControl();
        if (array_key_exists($control->getValue(), $this->strategies)) {
            $controlName = $control->getValue();
            throw new \InvalidArgumentException("Strategy for control '$controlName' already registered");
        }
        $this->strategies[$control->getValue()] = $strategy;
    }
}
