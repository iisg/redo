<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Symfony\Component\HttpFoundation\Request;

class MetadataValueProcessor {
    /** @var MetadataValueProcessorStrategy[] */
    private $strategies = [];

    public function process(array $values, string $control, Request $request): array {
        return array_key_exists($control, $this->strategies)
            ? $this->strategies[$control]->processValues($values, $request)
            : $values;
    }

    public function registerStrategy(MetadataValueProcessorStrategy $strategy) {
        $control = $strategy->getSupportedControl();
        if (array_key_exists($control, $this->strategies)) {
            throw new \InvalidArgumentException("Strategy for control '$control' already registered");
        }
        $this->strategies[$control] = $strategy;
    }
}
