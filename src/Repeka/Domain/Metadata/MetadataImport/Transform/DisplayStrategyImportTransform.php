<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Assert\Assertion;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

class DisplayStrategyImportTransform extends AbstractImportTransform {

    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $displayStrategyEvaluator) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
    }

    public function apply(array $values, array $config, array $dataBeingImported, string $parentMetadataValue = null): array {
        Assertion::keyExists($config, 'template');
        $template = $config['template'];
        $additionalContext = [
            'values' => $values,
            'data' => $dataBeingImported,
            'parentMetadataValue' => $parentMetadataValue,
        ];
        if (isset($config['separator'])) {
            $additionalContext['separator'] = $config['separator'];
        }
        $result = $this->displayStrategyEvaluator->render(
            null,
            $template,
            null,
            $additionalContext
        );
        if (isset($config['separator'])) {
            $result = explode($config['separator'], $result);
        }
        if (!is_array($result)) {
            $result = [$result];
        }
        return $result;
    }
}
